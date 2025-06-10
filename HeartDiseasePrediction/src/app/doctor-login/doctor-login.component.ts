import { NgIf } from '@angular/common';
import { Component } from '@angular/core';
import { ReactiveFormsModule, FormBuilder, FormGroup, Validators } from '@angular/forms';
import { RouterLink, Router } from '@angular/router';
import { HttpClient, HttpClientModule } from '@angular/common/http';

@Component({
  selector: 'app-doctor-login',
  standalone: true,
  imports: [RouterLink, ReactiveFormsModule, NgIf, HttpClientModule],
  templateUrl: './doctor-login.component.html',
  styleUrls: ['./doctor-login.component.scss']
})
export class DoctorLoginComponent {
  loginForm: FormGroup;
  submitted = false;

  constructor(
    private fb: FormBuilder,
    private http: HttpClient,
    private router: Router
  ) {
    this.loginForm = this.fb.group({
      doctorId: ['', [Validators.required, Validators.pattern(/^[0-9]{14}$/)]],
      email: ['', [Validators.required, Validators.email]],
      password: [
        '',
        [
          Validators.required,
          Validators.minLength(6),
          Validators.pattern(/^(?=.*[A-Z])(?=.*[!@#$%^&*(),.?":{}|<>])(?=.*\d).{6,}$/)
        ]
      ]
    });
  }

  onSubmit(): void {
    this.submitted = true;
    if (this.loginForm.invalid) {
      this.loginForm.markAllAsTouched();
      alert('❌ Please fill in all fields with valid information before submitting.');
      return;
    }

    const loginData = this.loginForm.value;
    console.log('Submitting login with:', loginData);

    this.http.post('http://localhost:8000/api/doctor/login', loginData).subscribe({
      next: (response: any) => {
        console.log('Login response:', response);
        if (response.token && response.user) {
          localStorage.setItem('doctorToken', response.token);
          localStorage.setItem('doctor', JSON.stringify(response.user));
          localStorage.setItem('user_role', 'doctor');
          this.loginForm.reset();
          this.submitted = false;
          console.log('Navigating to /doctor-home');
          this.router.navigate(['/doctor-home']).then(success => {
            console.log('Navigation successful:', success);
            if (success) {
              alert('✅ Doctor login successful!');
            }
          }).catch(err => {
            console.error('Navigation failed:', err);
            alert('❌ Navigation to Doctor Home failed: ' + err);
          });
        } else {
          console.warn('Invalid response:', response);
          alert('❌ Login failed: Missing token or user data');
        }
      },
      error: (error) => {
        const errorMessage = error.error?.error || 'An error occurred during login.';
        console.error('Login error:', error);
        alert('❌ Login failed: ' + errorMessage);
      }
    });
  }

  isInvalid(field: string): boolean {
    const control = this.loginForm.get(field);
    return control ? control.invalid && (control.touched || this.submitted) : false;
  }

  getError(field: string): string | null {
    const control = this.loginForm.get(field);
    if (!control) {
      return null; // Explicitly handle null control
    }
    if (control.hasError('required') && (control.touched || this.submitted)) {
      return 'This field is required.';
    }
    if (control.hasError('pattern')) {
      if (field === 'doctorId') {
        return '❌ ID must be exactly 14 digits.';
      } else if (field === 'password') {
        return '❌ Password must include at least 1 uppercase, 1 special char, and 1 number.';
      }
    }
    if (control.hasError('email')) {
      return '❌ Enter a valid email address.';
    }
    if (control.hasError('minlength')) {
      const requiredLength = control.getError('minlength')?.requiredLength;
      return `Must be at least ${requiredLength} characters long.`;
    }
    return null; // Default return
  }
}