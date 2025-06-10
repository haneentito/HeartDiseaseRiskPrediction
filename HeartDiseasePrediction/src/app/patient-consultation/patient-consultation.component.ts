import { Component, OnInit } from '@angular/core';
import { HttpClient, HttpClientModule, HttpHeaders } from '@angular/common/http';
import { NgFor, NgClass, CommonModule } from '@angular/common';
import { RouterLink, Router } from '@angular/router';
import { JwtHelperService, JWT_OPTIONS } from '@auth0/angular-jwt';

interface Doctor {
  id: string;
  name: string;
  rating: number;
}

@Component({
  selector: 'app-patient-consultation',
  standalone: true,
  imports: [HttpClientModule, NgFor, NgClass, RouterLink, CommonModule],
  providers: [
    JwtHelperService,
    { provide: JWT_OPTIONS, useValue: { tokenGetter: () => localStorage.getItem('token') } }
  ],
  templateUrl: './patient-consultation.component.html',
  styleUrl: './patient-consultation.component.scss'
})
export class PatientConsultationComponent implements OnInit {
  doctors: Doctor[] = [];
  isLoading: boolean = false;
  errorMessage: string | null = null;

  constructor(
    private http: HttpClient,
    private jwtHelper: JwtHelperService,
    private router: Router
  ) {}

  ngOnInit(): void {
    this.fetchDoctors();
  }

  fetchDoctors(): void {
    this.isLoading = true;
    this.errorMessage = null;

    this.http.get<Doctor[]>('http://localhost:8000/api/doctors').subscribe({
      next: (response) => {
        console.log('Fetched doctors:', response); // Debug log
        this.doctors = response;
        this.isLoading = false;
      },
      error: (error) => {
        console.error('Error fetching doctors:', error);
        this.errorMessage = 'Failed to load doctors. Please try again later.';
        this.isLoading = false;
      }
    });
  }

  rateDoctor(doctorId: string, rating: number): void {
    const doctor = this.doctors.find(d => d.id === doctorId);
    if (!doctor) {
      this.errorMessage = 'Doctor not found.';
      return;
    }

    const token = localStorage.getItem('token');
    let patientId: string | null = null;
    if (token) {
      try {
        const decodedToken = this.jwtHelper.decodeToken(token);
        patientId = decodedToken.sub || null;

        if (this.jwtHelper.isTokenExpired(token)) {
          localStorage.removeItem('token');
          this.errorMessage = 'Your session has expired. Please log in to rate a doctor.';
          this.router.navigate(['/patient-login']);
          return;
        }
      } catch (error) {
        console.error('Error decoding token:', error);
        localStorage.removeItem('token');
        this.errorMessage = 'Invalid session token. Please log in to rate a doctor.';
        this.router.navigate(['/patient-login']);
        return;
      }
    }
    if (!patientId) {
      this.errorMessage = 'You need to log in to rate a doctor.';
      this.router.navigate(['/patient-login']);
      return;
    }

    const headers = new HttpHeaders({ 'Authorization': `Bearer ${token}` });

    this.http.post<any>('http://localhost:8000/api/doctors/rate', { doctor_id: doctorId, patient_id: patientId, rating }, { headers }).subscribe({
      next: (response) => {
        alert('Rating submitted successfully!');
        this.fetchDoctors();
      },
      error: (error) => {
        console.error('Error submitting rating:', error);
        if (error.status === 401) {
          localStorage.removeItem('token');
          this.errorMessage = 'Your session has expired. Please log in to rate a doctor.';
          this.router.navigate(['/patient-login']);
        } else {
          this.errorMessage = error.status === 422 
            ? 'Failed to submit rating due to validation errors.' 
            : 'Failed to submit rating. Please try again.';
        }
      }
    });
  }

  getStarCount(rating: number): number {
    return Math.floor(Number(rating));
  }
}