import { NgIf } from '@angular/common';
import { Component } from '@angular/core';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators, AbstractControl, ValidationErrors } from '@angular/forms';
import { HttpClient, HttpClientModule } from '@angular/common/http';
import { Router } from '@angular/router';

@Component({
  selector: 'app-patient-registration',
  standalone: true,
  imports: [ReactiveFormsModule, NgIf, HttpClientModule],
  templateUrl: './patient-registration.component.html',
  styleUrls: ['./patient-registration.component.scss']
})
export class PatientRegistrationComponent {
  registerForm: FormGroup;
  submitted = false;

  constructor(
    private fb: FormBuilder,
    private http: HttpClient,
    private router: Router
  ) {
    this.registerForm = this.fb.group({
      name: [
        '',
        [
          Validators.required,
          Validators.minLength(3),
          Validators.pattern(/^[a-zA-Z\s]+$/)
        ]
      ],
      email: ['', [Validators.required, Validators.pattern(/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/)]],
      phone: ['', [Validators.required, Validators.pattern('^[0-9]{10,12}$')]],
      password: [
        '',
        [
          Validators.required,
          Validators.minLength(6),
          Validators.pattern(/^(?=.*[A-Z])(?=.*[!@#$%^&*(),.?":{}|<>])(?=.*\d).{6,}$/)
        ]
      ],
      confirmPassword: ['', [Validators.required]],
    }, { validators: this.matchPasswordsValidator });
  }

  matchPasswordsValidator(group: AbstractControl): ValidationErrors | null {
    const password = group.get('password')?.value;
    const confirmPassword = group.get('confirmPassword')?.value;
    return password === confirmPassword ? null : { passwordsMismatch: true };
  }

  onSubmit(): void {
    this.submitted = true;
    if (this.registerForm.invalid) {
      alert('❌ ❌ You Must fill in all fields Correctly Before Submitting.');
      return;
    }

    const { confirmPassword, ...formData } = this.registerForm.value;

    this.http.post('http://localhost:8000/api/register', formData).subscribe({
      next: (response) => {
        alert('Registration successful!');
        const loginData = {
          email: formData.email,
          password: formData.password
        };
        this.http.post('http://localhost:8000/api/login', loginData, { withCredentials: false }).subscribe({
          next: (loginResponse: any) => {
            localStorage.setItem('patientToken', loginResponse.token);
            localStorage.setItem('patient', JSON.stringify(loginResponse.user));
            localStorage.setItem('user_role', 'patient');
            this.registerForm.reset();
            this.submitted = false;
            this.router.navigate(['/patient-home']);
          },
          error: (loginError) => {
            let errorMessage = '❌ Auto-login failed: ';
            if (loginError.status === 422 || loginError.status === 401 || loginError.status === 500) {
              errorMessage += loginError.error.error || 'An error occurred during login.';
            } else {
              errorMessage += 'Please log in manually.';
            }
            alert(errorMessage);
            console.error('Auto-login error:', loginError);
            this.router.navigate(['/patient-login']);
          }
        });
      },
      error: (error) => {
        console.error('Registration error:', error, 'URL:', 'http://localhost:8000/api/register'); // Log URL for debugging
        if (error.status === 404) {
          alert('❌ Registration failed: Endpoint not found. Check server configuration.');
        } else if (error.status === 422) {
          const errors = error.error.error || error.error.errors;
          let errorMessage = '❌ Registration failed:\n';
          if (typeof errors === 'string') {
            errorMessage += errors;
          } else {
            for (const field in errors) {
              errorMessage += `${errors[field].join(', ')}\n`;
            }
          }
          alert(errorMessage);
        } else {
          alert('❌ Registration failed. Please try again.');
        }
      }
    });
  }

  isInvalid(field: string): boolean {
    const control = this.registerForm.get(field);
    return control ? control.invalid && (control.touched || this.submitted) : false;
  }

  getError(field: string): string | null {
    const control = this.registerForm.get(field);
    if (control?.hasError('required') && (control.touched || this.submitted)) {
      return 'This field is required.';
    }
    if (control?.hasError('minlength')) {
      const requiredLength = control.getError('minlength')?.requiredLength;
      return `Must be at least ${requiredLength} characters long.`;
    }
    if (control?.hasError('pattern')) {
      switch (field) {
        case 'name':
          return '❌ Only letters and spaces are allowed.';
        case 'email':
          return '❌ Enter a valid email address.';
        case 'phone':
          return '❌ Only 10-12 digits are allowed.';
        case 'password':
          return '❌ Password must include at least 1 uppercase letter, 1 special character, and 1 number.';
      }
    }
    if (field === 'confirmPassword' && this.registerForm.hasError('passwordsMismatch')) {
      return '❌ Confirm Password Must Match Password.';
    }
    return null;
  }
}