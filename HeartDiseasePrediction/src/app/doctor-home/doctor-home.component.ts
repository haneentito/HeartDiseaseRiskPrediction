import { Component, OnInit } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Router } from '@angular/router';
import { RouterModule } from '@angular/router';
import { HttpClientModule } from '@angular/common/http';
import { CommonModule } from '@angular/common';
import { DoctorNavbarComponent } from '../doctor-navbar/doctor-navbar.component';

export interface Notification {
  id: number;
  message: string;
  appointment_id: number;
}

@Component({
  selector: 'app-doctor-home',
  templateUrl: './doctor-home.component.html',
  styleUrls: ['./doctor-home.component.scss'],
  standalone: true,
  imports: [RouterModule, HttpClientModule, CommonModule, ]
})
export class DoctorHomeComponent implements OnInit {
  notifications: Notification[] = [];
  showNotifications: boolean = false;
  isLoadingNotifications: boolean = true;

  constructor(private http: HttpClient, private router: Router) {}

  ngOnInit(): void {
    this.fetchNotifications();
  }

  fetchNotifications(): void {
    const token = localStorage.getItem('doctorToken');
    if (!token) {
      alert('No doctorToken found. Redirecting to login.');
      this.router.navigate(['/doctor-login']);
      return;
    }
    console.log('Token payload:', JSON.parse(atob(token.split('.')[1])));
    const headers = new HttpHeaders({ 'Authorization': `Bearer ${token}` });
    this.isLoadingNotifications = true;
    this.http.get<Notification[]>('http://localhost:8000/api/doctor/notifications', { headers }).subscribe(
      (response) => {
        this.notifications = response;
        console.log('Notifications fetched:', response);
        this.isLoadingNotifications = false;
      },
      (error) => {
        console.error('Error fetching notifications:', error);
        if (error.status === 401) {
          alert('Session expired. Redirecting to login.');
          this.router.navigate(['/doctor-login']);
        } else {
          alert('Failed to fetch notifications. Try again.');
        }
        this.isLoadingNotifications = false;
      }
    );
  }

  toggleNotifications(event: MouseEvent): void {
    event.preventDefault();
    this.showNotifications = !this.showNotifications;
    if (this.showNotifications) {
      this.fetchNotifications(); // Refresh notifications when opening
    }
  }

  acceptAppointment(appointmentId: number): void {
    const token = localStorage.getItem('doctorToken');
    if (!token) {
      alert('Please log in to accept appointments.');
      this.router.navigate(['/doctor-login']);
      return;
    }

    const headers = new HttpHeaders({ 'Authorization': `Bearer ${token}` });
    this.http.post(`http://localhost:8000/api/doctor/appointments/${appointmentId}/accept`, {}, { headers }).subscribe(
      () => {
        this.notifications = this.notifications.filter(n => n.appointment_id !== appointmentId);
        this.fetchNotifications();
        alert('Appointment accepted successfully!');
      },
      (error) => {
        console.error('Error accepting appointment:', error);
        if (error.status === 401) {
          alert('Session expired. Please log in again.');
          this.router.navigate(['/doctor-login']);
        } else {
          alert('Failed to accept appointment. Try again.');
        }
      }
    );
  }

  declineAppointment(appointmentId: number): void {
    const token = localStorage.getItem('doctorToken');
    if (!token) {
      alert('Please log in to decline appointments.');
      this.router.navigate(['/doctor-login']);
      return;
    }

    const headers = new HttpHeaders({ 'Authorization': `Bearer ${token}` });
    this.http.post(`http://localhost:8000/api/doctor/appointments/${appointmentId}/decline`, {}, { headers }).subscribe(
      () => {
        this.notifications = this.notifications.filter(n => n.appointment_id !== appointmentId);
        this.fetchNotifications();
        alert('Appointment declined successfully!');
      },
      (error) => {
        console.error('Error declining appointment:', error);
        if (error.status === 401) {
          alert('Session expired. Please log in again.');
          this.router.navigate(['/doctor-login']);
        } else {
          alert('Failed to decline appointment. Try again.');
        }
      }
    );
  }

  logout(): void {
    localStorage.removeItem('doctorToken');
    localStorage.removeItem('doctor');
    localStorage.removeItem('user_role');
    this.router.navigate(['/doctor-login']);
  }
}