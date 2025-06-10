import { Component, OnInit } from '@angular/core';
import { HttpClient, HttpClientModule } from '@angular/common/http';
import { Router, ActivatedRoute, RouterLink } from '@angular/router';
import { ReactiveFormsModule, FormBuilder, FormGroup, Validators } from '@angular/forms';
import { NgIf } from '@angular/common';

@Component({
  selector: 'app-forget-password',
  standalone: true,
  imports: [RouterLink, ReactiveFormsModule, NgIf, HttpClientModule],
  templateUrl: './forget-password.component.html',
  styleUrls: ['./forget-password.component.scss']
})
export class ForgetPasswordComponent implements OnInit {
  forgetForm: FormGroup;
  submitted = false;
  message: string | null = null;
  errorMessage: string | null = null;
  userType: string = '';

  constructor(
    private fb: FormBuilder,
    private http: HttpClient,
    private router: Router,
    private route: ActivatedRoute
  ) {
    this.forgetForm = this.fb.group({
      email: ['', [Validators.required, Validators.email]]
    });
  }

  ngOnInit(): void {
    this.route.queryParams.subscribe(params => {
      this.userType = params['userType'] || 'doctor';
    });
  }

  requestReset(): void {
    this.submitted = true;
    this.message = null;
    this.errorMessage = null;

    if (this.forgetForm.invalid) {
      this.forgetForm.markAllAsTouched();
      this.errorMessage = '❌ Please enter a valid email address.';
      return;
    }

    const email = this.forgetForm.value.email;
    const endpoint = this.userType === 'doctor'
      ? 'http://localhost:8000/api/doctor/password/reset-request'
      : 'http://localhost:8000/api/password/reset-request';

    this.http.post<{ message: string }>(endpoint, { email }).subscribe(
      (response) => {
        this.message = `${response.message} Check the Laravel log (storage/logs/laravel.log) for the password reset link.`;
        setTimeout(() => {
          this.router.navigate([`/${this.userType}-login`]);
        }, 2000);
      },
      (error) => {
        console.error('Forget Password error:', error);
        this.errorMessage = error.error?.error || 'Failed to request password reset. Please try again.';
      }
    );
  }

  isInvalid(field: string): boolean {
    const control = this.forgetForm.get(field);
    return control ? control.invalid && (control.touched || this.submitted) : false;
  }

  getError(field: string): string | null {
    const control = this.forgetForm.get(field);
    if (control?.hasError('required') && (control.touched || this.submitted)) {
      return 'This field is required.';
    }
    if (control?.hasError('email')) {
      return '❌ Enter a valid email address.';
    }
    return null;
  }
}