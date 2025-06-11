import { Component, OnInit } from '@angular/core';
import { HttpClient, HttpClientModule } from '@angular/common/http';
import { Router, ActivatedRoute, RouterLink } from '@angular/router';
import { ReactiveFormsModule, FormBuilder, FormGroup, Validators, AbstractControl, ValidationErrors } from '@angular/forms';
import { NgIf } from '@angular/common';

@Component({
  selector: 'app-reset-password',
  standalone: true,
  imports: [RouterLink, ReactiveFormsModule, NgIf, HttpClientModule],
  templateUrl: './reset-password.component.html',
  styleUrls: ['./reset-password.component.scss']
})
export class ResetPasswordComponent implements OnInit {
  resetForm: FormGroup;
  submitted = false;
  message: string | null = null;
  errorMessage: string | null = null;
  userType: string = '';
  token: string | null = null;
  email: string | null = null;

  constructor(
    private fb: FormBuilder,
    private http: HttpClient,
    private router: Router,
    private route: ActivatedRoute
  ) {
    this.resetForm = this.fb.group({
      email: ['', [Validators.required, Validators.email]],
      password: [
        '',
        [
          Validators.required,
          Validators.minLength(6),
          Validators.pattern(/^(?=.*[A-Z])(?=.*[!@#$%^&*(),.?":{}|<>])(?=.*\d).{6,}$/)
        ]
      ],
      confirmPassword: ['', [Validators.required]]
    }, { validators: this.matchPasswordsValidator });
  }

  ngOnInit(): void {
    this.route.queryParams.subscribe(params => {
      this.userType = params['userType'] || 'doctor';
      this.token = params['token'] || null;
      this.email = params['email'] || null;
      if (this.email) {
        this.resetForm.patchValue({ email: this.email });
      }
    });
  }

  matchPasswordsValidator(group: AbstractControl): ValidationErrors | null {
    const password = group.get('password')?.value;
    const confirmPassword = group.get('confirmPassword')?.value;
    return password === confirmPassword ? null : { passwordsMismatch: true };
  }

  onSubmit(): void {
    this.submitted = true;
    this.message = null;
    this.errorMessage = null;

    if (this.resetForm.invalid || !this.token) {
      this.errorMessage = '❌ Please fill in all fields correctly.';
      return;
    }

    const { email, password } = this.resetForm.value;
    const endpoint = this.userType === 'doctor'
      ? 'http://localhost:8000/api/doctor/password/reset'
      : 'http://localhost:8000/api/password/reset';

    this.http.post<{ message: string }>(endpoint, {
      email,
      token: this.token,
      password
    }).subscribe(
      (response) => {
        this.message = response.message;
        setTimeout(() => {
          this.router.navigate([`/${this.userType}-login`]);
        }, 2000);
      },
      (error) => {
        console.error('Reset Password error:', error);
        this.errorMessage = error.error?.error || 'Failed to reset password. The link may be invalid or expired.';
      }
    );
  }

  isInvalid(field: string): boolean {
    const control = this.resetForm.get(field);
    return control ? control.invalid && (control.touched || this.submitted) : false;
  }

  getError(field: string): string | null {
    const control = this.resetForm.get(field);
    if (control?.hasError('required') && (control.touched || this.submitted)) {
      return 'This field is required.';
    }
    if (control?.hasError('email')) {
      return '❌ Enter a valid email address.';
    }
    if (control?.hasError('minlength')) {
      const requiredLength = control.getError('minlength')?.requiredLength;
      return `Must be at least ${requiredLength} characters long.`;
    }
    if (control?.hasError('pattern') && field === 'password') {
      return '❌ Password must include at least 1 uppercase letter, 1 special character, and 1 number.';
    }
    if (field === 'confirmPassword' && this.resetForm.hasError('passwordsMismatch')) {
      return '❌ Confirm Password must match Password.';
    }
    return null;
  }
}
