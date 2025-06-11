import { Component, OnInit } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Router, RouterLink, RouterLinkActive } from '@angular/router';
import { NgIf, NgFor } from '@angular/common';

interface Notification {
  id: number;
  message: string;
  appointment_id: number;
}

@Component({
  selector: 'app-doctor-nav',
  standalone: true,
  imports: [RouterLink, RouterLinkActive, NgIf, NgFor],
  templateUrl: './doctor-navbar.component.html',
  styleUrl: './doctor-navbar.component.scss'
})
export class DoctorNavbarComponent implements OnInit {
  notifications: Notification[] = [];
  showNotifications: boolean = false;

  constructor(private http: HttpClient, private router: Router) {}

  ngOnInit(): void {
    this.fetchNotifications();
  }

  fetchNotifications(): void {
    const token = localStorage.getItem('doctorToken');
    if (!token) {
      console.error('No doctorToken found. Redirecting to login.');
      this.router.navigate(['/doctor-login']);
      return;
    }

    const headers = new HttpHeaders({
      'Authorization': `Bearer ${token}`
    });

    this.http.get<Notification[]>('http://localhost:8000/api/doctor/notifications', { headers }).subscribe(
      (response) => {
        this.notifications = response;
      },
      (error) => {
        console.error('Error fetching notifications:', error);
        if (error.status === 401) {
          this.router.navigate(['/doctor-login']);
        }
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
      this.router.navigate(['/doctor-login']);
      return;
    }

    const headers = new HttpHeaders({
      'Authorization': `Bearer ${token}`
    });

    this.http.post(`http://localhost:8000/api/doctor/appointments/${appointmentId}/accept`, {}, { headers }).subscribe(
      () => {
        this.notifications = this.notifications.filter(n => n.appointment_id !== appointmentId);
        this.fetchNotifications(); // Refresh notifications
      },
      (error) => {
        console.error('Error accepting appointment:', error);
      }
    );
  }

  declineAppointment(appointmentId: number): void {
    const token = localStorage.getItem('doctorToken');
    if (!token) {
      this.router.navigate(['/doctor-login']);
      return;
    }

    const headers = new HttpHeaders({
      'Authorization': `Bearer ${token}`
    });

    this.http.post(`http://localhost:8000/api/doctor/appointments/${appointmentId}/decline`, {}, { headers }).subscribe(
      () => {
        this.notifications = this.notifications.filter(n => n.appointment_id !== appointmentId);
        this.fetchNotifications(); // Refresh notifications
      },
      (error) => {
        console.error('Error declining appointment:', error);
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