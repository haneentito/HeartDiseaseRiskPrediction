import { NgIf } from '@angular/common';
import { Component } from '@angular/core';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators, AbstractControl, ValidationErrors } from '@angular/forms';
import { HttpClient, HttpClientModule } from '@angular/common/http';
import { Router } from '@angular/router';

@Component({
  selector: 'app-doctor-registration',
  standalone: true,
  imports: [ReactiveFormsModule, NgIf, HttpClientModule],
  templateUrl: './doctor-registration.component.html',
  styleUrls: ['./doctor-registration.component.scss']
})
export class DoctorRegistrationComponent {
  registerForm: FormGroup;
  submitted = false;

  constructor(
    private fb: FormBuilder,
    private http: HttpClient,
    private router: Router
  ) {
    this.registerForm = this.fb.group({
      doctorId: ['', [Validators.required, Validators.pattern(/^[0-9]{14}$/)]], // Exactly 14 digits
      name: [
        '',
        [
          Validators.required,
          Validators.minLength(5), // Updated to match backend (min:5)
          Validators.pattern(/^[a-zA-Z\s]+$/)
        ]
      ],
      email: ['', [Validators.required, Validators.email]],
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
    alert('❌ Please fill in all fields correctly before submitting.');
    return;
  }

  const formData = this.registerForm.value;
  console.log('Form data being sent:', formData); // Debug the payload

  this.http.post('http://localhost:8000/api/doctor/register', formData).subscribe({
    next: (response) => {
      alert('✅ Registration successful!');
      const loginData = {
        doctorId: formData.doctorId,
        password: formData.password
      };
      this.http.post('http://localhost:8000/api/doctor/login', loginData, { withCredentials: false }).subscribe({
        next: (loginResponse: any) => {
          localStorage.setItem('doctorToken', loginResponse.token);
          localStorage.setItem('doctor', JSON.stringify(loginResponse.user));
          localStorage.setItem('user_role', 'doctor');
          this.registerForm.reset();
          this.submitted = false;
          this.router.navigate(['/doctor-home']);
        },
        error: (loginError) => {
          let errorMessage = '❌ Auto-login failed: ';
          if (loginError.status === 422 || loginError.status === 401 || loginError.status === 500) {
            errorMessage += loginError.error?.error || JSON.stringify(loginError.error) || 'An error occurred during login.';
          } else {
            errorMessage += 'Please log in manually.';
          }
          alert(errorMessage);
          console.error('Auto-login error:', loginError);
          this.router.navigate(['/doctor-login']);
        }
      });
    },
    error: (error) => {
      if (error.status === 422) {
        const errors = error.error.errors || {};
        let errorMessage = '❌ Registration failed:\n';
        for (const field in errors) {
          errorMessage += `${field}: ${errors[field].join(', ')}\n`;
        }
        alert(errorMessage);
        console.log('Validation errors:', errors); // Debug the errors
      } else {
        alert('❌ Registration failed. Please try again.');
      }
      console.error('Registration error:', error);
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
        case 'doctorId':
          return '❌ Doctor ID must be exactly 14 digits.';
        case 'name':
          return '❌ Only letters and spaces are allowed.';
        case 'email':
          return '❌ Enter a valid email address.';
        case 'phone':
          return '❌ Phone must be 10-12 digits.';
        case 'password':
          return '❌ Password must include at least 1 uppercase letter, 1 special character, and 1 number.';
      }
    }
    if (field === 'confirmPassword' && this.registerForm.hasError('passwordsMismatch')) {
      return '❌ Confirm Password must match Password.';
    }
    return null;
  }
}