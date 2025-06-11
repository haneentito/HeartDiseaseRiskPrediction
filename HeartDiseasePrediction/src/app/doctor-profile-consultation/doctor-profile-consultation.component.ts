import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule, ActivatedRoute, Router } from '@angular/router';
import { HttpClient, HttpHeaders, HttpClientModule } from '@angular/common/http';
import { JwtHelperService } from '@auth0/angular-jwt';

@Component({
  selector: 'app-doctor-profile-consultation',
  standalone: true,
  imports: [CommonModule, RouterModule, HttpClientModule],
  templateUrl: './doctor-profile-consultation.component.html',
  styleUrls: ['./doctor-profile-consultation.component.scss']
})
export class DoctorProfileConsultationComponent implements OnInit {
  doctor: any = {};
  schedule: any[] = [];
  groupedSchedule: { [key: string]: any[] } = {
    'Monday': [],
    'Tuesday': [],
    'Wednesday': [],
    'Thursday': [],
    'Sunday': []
  };
  days: string[] = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Sunday'];
  selectedSlot: any = null;
  errorMessage: string | null = null;
  successMessage: string | null = null;
  isLoadingDoctor: boolean = true;
  isLoadingSchedule: boolean = true;
  jwtHelper: JwtHelperService = new JwtHelperService();

  constructor(
    private route: ActivatedRoute,
    private http: HttpClient,
    private router: Router
  ) {}

  ngOnInit(): void {
    const doctorId = this.route.snapshot.paramMap.get('id');
    console.log('DoctorProfileConsultationComponent initialized with doctorId:', doctorId);
    if (doctorId) {
      this.fetchDoctor(doctorId);
      this.fetchSchedule(doctorId);
    } else {
      console.error('No doctorId provided in route');
      this.errorMessage = 'Doctor ID not provided.';
      this.router.navigate(['/patient-consultation']);
    }
  }

  fetchDoctor(doctorId: string): void {
    this.isLoadingDoctor = true;
    console.log('Fetching doctor with ID:', doctorId);
    this.http.get<any>(`http://localhost:8000/api/doctors/${doctorId}`).subscribe({
      next: (response) => {
        console.log('Doctor fetch successful:', response);
        this.doctor = response;
        this.isLoadingDoctor = false;
      },
      error: (error) => {
        console.error('Error fetching doctor:', error);
        this.errorMessage = 'Failed to load doctor profile. Please try again.';
        this.isLoadingDoctor = false;
      }
    });
  }

  fetchSchedule(doctorId: string): void {
    this.isLoadingSchedule = true;
    console.log('Fetching schedule for doctorId:', doctorId);
    this.http.get<any[]>(`http://localhost:8000/api/doctors/${doctorId}/schedule`).subscribe({
      next: (response) => {
        console.log('Schedule fetch successful:', response);
        this.schedule = response.filter(slot => slot.available === true); // Only keep available slots
        this.groupScheduleByDay();
        this.isLoadingSchedule = false;
      },
      error: (error) => {
        console.error('Error fetching schedule:', error);
        this.errorMessage = 'Failed to load schedule. Please try again.';
        this.isLoadingSchedule = false;
      }
    });
  }

  groupScheduleByDay(): void {
    this.days.forEach(day => {
      this.groupedSchedule[day] = this.schedule.filter(slot => slot.day === day) || [];
    });
  }

  formatTime(time: string): string {
    const [hours, minutes] = time.split(':');
    const hourNum = parseInt(hours, 10);
    const period = hourNum >= 12 ? 'PM' : 'AM';
    const adjustedHour = hourNum % 12 || 12;
    return `${adjustedHour}:${minutes} ${period}`;
  }

  selectSlot(slot: any): void {
    this.selectedSlot = slot;
  }

  rateDoctor(star: number): void {
    const doctorId = this.route.snapshot.paramMap.get('id');
    if (!doctorId) {
      this.errorMessage = 'Doctor ID not provided.';
      return;
    }

    const token = localStorage.getItem('token');
    if (!token) {
      this.errorMessage = 'No token found. Please log in as a patient.';
      this.router.navigate(['/patient-login']);
      return;
    }

    let patientId: string | null = null;
    try {
      const decodedToken = this.jwtHelper.decodeToken(token);
      patientId = decodedToken.sub || null;
      if (!patientId) {
        this.errorMessage = 'Invalid token: Patient ID not found.';
        this.router.navigate(['/patient-login']);
        return;
      }
      if (this.jwtHelper.isTokenExpired(token)) {
        this.errorMessage = 'Session expired. Please log in again.';
        localStorage.removeItem('token');
        this.router.navigate(['/patient-login']);
        return;
      }
    } catch (error) {
      console.error('Error decoding token:', error);
      this.errorMessage = 'Invalid token. Please log in again.';
      this.router.navigate(['/patient-login']);
      return;
    }

    const ratingData = {
      doctor_id: doctorId,
      patient_id: patientId,
      rating: star
    };

    const headers = new HttpHeaders({
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    });

    this.http.post<any>('http://localhost:8000/api/doctors/rate', ratingData, { headers }).subscribe({
      next: (response) => {
        this.successMessage = 'Rating submitted successfully!';
        this.errorMessage = null;
        this.fetchDoctor(doctorId);
      },
      error: (error) => {
        console.error('Error submitting rating:', error);
        this.errorMessage = error.status === 401 || error.status === 403
          ? 'Unauthorized. Please log in again.'
          : 'Failed to submit rating. Please try again.';
        if (error.status === 401 || error.status === 403) {
          localStorage.removeItem('token');
          this.router.navigate(['/patient-login']);
        }
      }
    });
  }

  submitAppointment(): void {
    if (!this.selectedSlot) {
      this.errorMessage = 'Please select a time slot.';
      this.successMessage = null;
      return;
    }

    const doctorId = this.route.snapshot.paramMap.get('id');
    if (!doctorId) {
      this.errorMessage = 'Doctor ID not provided.';
      this.successMessage = null;
      return;
    }

    const token = localStorage.getItem('token');
    if (!token) {
      this.errorMessage = 'No token found. Please log in as a patient.';
      this.successMessage = null;
      this.router.navigate(['/patient-login']);
      return;
    }

    let patientId: string | null = null;
    try {
      const decodedToken = this.jwtHelper.decodeToken(token);
      console.log('Decoded token details:', decodedToken);
      patientId = decodedToken.sub || null;
      if (!patientId) {
        this.errorMessage = 'Invalid token: Patient ID not found.';
        this.successMessage = null;
        this.router.navigate(['/patient-login']);
        return;
      }
      if (this.jwtHelper.isTokenExpired(token)) {
        console.log('Token is expired at:', new Date(this.jwtHelper.getTokenExpirationDate(token)!.getTime()));
        this.errorMessage = 'Session expired. Please log in again.';
        localStorage.removeItem('token');
        this.router.navigate(['/patient-login']);
        return;
      }
    } catch (error) {
      console.error('Error decoding token:', error);
      this.errorMessage = 'Invalid token. Please log in again.';
      this.successMessage = null;
      this.router.navigate(['/patient-login']);
      return;
    }

    const appointmentData = {
      doctor_id: doctorId,
      patient_id: patientId,
      schedule_id: this.selectedSlot.id,
      day: this.selectedSlot.day,
      time: this.selectedSlot.time
    };

    const headers = new HttpHeaders({
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    });
    const apiUrl = 'http://localhost:8000/api/patient/storeAppointment';

    console.log('Submitting appointment - URL:', apiUrl);
    console.log('Submitting appointment - Headers:', headers);
    console.log('Submitting appointment - Body:', appointmentData);

    this.http.post<any>(apiUrl, appointmentData, { headers }).subscribe({
      next: (response) => {
        console.log('Appointment submission successful:', response);
        this.successMessage = 'Appointment booked successfully!';
        this.errorMessage = null;
        this.selectedSlot = null;
        this.fetchSchedule(doctorId);
        // Optionally add a delay or manual redirect
        // setTimeout(() => this.router.navigate(['/patient-home']), 3000); // 3-second delay
      },
      error: (error) => {
        console.error('Error submitting appointment - Full error:', error);
        this.successMessage = null;
        this.errorMessage = error.status === 422
          ? 'Failed to submit appointment due to validation errors. Please try again.'
          : (error.status === 401 || error.status === 403
            ? 'Unauthorized: Your session may be invalid or expired. Please log in again.'
            : 'Failed to submit appointment. Please try again.');
        if (error.status === 401 || error.status === 403) {
          localStorage.removeItem('token');
          this.router.navigate(['/patient-login']);
        }
      }
    });
  }

  redirectToHome(): void {
    this.router.navigate(['/patient-home']);
    this.successMessage = null; // Clear the message after redirect
  }
}