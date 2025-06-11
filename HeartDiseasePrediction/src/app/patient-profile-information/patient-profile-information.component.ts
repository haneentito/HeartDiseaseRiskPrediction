import { Component, OnInit } from '@angular/core';
import { HttpClient, HttpClientModule, HttpHeaders } from '@angular/common/http';
import { NgIf } from '@angular/common';
import { RouterLink, Router } from '@angular/router';
import { CommonModule, DatePipe } from '@angular/common';

interface Patient {
  id: number;
  name: string;
  email: string;
  phone: string;
  address?: string; // Optional since it might not exist
  date_of_birth?: string; // Optional since it might not exist
  created_at: string;
  role: string;
}

@Component({
  selector: 'app-patient-profile-information',
  standalone: true,
  imports: [HttpClientModule, NgIf, RouterLink, CommonModule],
  templateUrl: './patient-profile-information.component.html',
  styleUrl: './patient-profile-information.component.scss'
})
export class PatientProfileInformationComponent implements OnInit {
  patient: Patient | null = null;
  isLoading: boolean = false;
  errorMessage: string | null = null;

  constructor(private http: HttpClient, private router: Router) {}

  ngOnInit(): void {
    this.fetchPatientProfile();
  }

  fetchPatientProfile(): void {
    this.isLoading = true;
    this.errorMessage = null;

    const token = localStorage.getItem('token');
    if (!token) {
      this.errorMessage = 'You are not authenticated. Please log in.';
      this.isLoading = false;
      this.router.navigate(['/patient-login']);
      return;
    }

    const headers = new HttpHeaders({
      'Authorization': `Bearer ${token}`
    });

    const apiUrl = 'http://localhost:8000/api/profile';
    this.http.get<{ message: string, user: Patient }>(apiUrl, { headers }).subscribe({
      next: (response) => {
        this.patient = response.user;
        this.isLoading = false;
      },
      error: (error) => {
        console.error('Error fetching patient profile:', error);
        this.errorMessage = error.error?.error || 'Failed to load profile. Please try again later.';
        this.patient = null;
        this.isLoading = false;
        if (error.status === 401) {
          localStorage.removeItem('token');
          localStorage.removeItem('user');
          this.router.navigate(['/patient-login']);
        }
      }
    });
  }
}
