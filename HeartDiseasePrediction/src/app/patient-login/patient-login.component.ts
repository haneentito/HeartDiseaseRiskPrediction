import { Component } from '@angular/core';
import { HttpClient, HttpClientModule } from '@angular/common/http';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { Router, RouterLink } from '@angular/router';
import { NgIf } from '@angular/common';

@Component({
  selector: 'app-patient-login',
  standalone: true,
  imports: [ReactiveFormsModule, HttpClientModule, RouterLink, NgIf],
  templateUrl: './patient-login.component.html',
  styleUrls: ['./patient-login.component.scss']
})
export class PatientLoginComponent {
  loginForm: FormGroup;
  errorMessage: string | null = null;

  constructor(private fb: FormBuilder, private http: HttpClient, private router: Router) {
    this.loginForm = this.fb.group({
      email: ['', [Validators.required, Validators.email]],
      password: ['', [Validators.required, Validators.minLength(6)]]
    });
  }

  onSubmit(): void {
    if (this.loginForm.invalid) {
      this.errorMessage = 'Please fill in all fields with valid information.';
      return;
    }

    const loginData = this.loginForm.value;

    this.http.post<any>('http://localhost:8000/api/login', loginData).subscribe({
      next: (response) => {
        localStorage.setItem('token', response.token);
        this.router.navigate(['/patient-home']); // Changed to /patient-home
      },
      error: (error) => {
        console.error('Login error:', error);
        this.errorMessage = error.status === 401 
          ? 'Invalid email or password.' 
          : 'An error occurred. Please try again later.';
      }
    });
  }

  getError(field: string): string | null {
    const control = this.loginForm.get(field);
    if (control?.hasError('required') && control.touched) {
      return 'This field is required.';
    }
    if (control?.hasError('email')) {
      return 'Please enter a valid email address.';
    }
    if (control?.hasError('minlength')) {
      return 'Password must be at least 6 characters.';
    }
    return null;
  }
}