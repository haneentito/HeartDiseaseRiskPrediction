import { Component, OnInit } from '@angular/core';
import { HttpClient, HttpClientModule, HttpHeaders } from '@angular/common/http';
import { NgIf, NgFor } from '@angular/common';
import { Router } from '@angular/router';

interface Appointment {
    id: number;
    doctor_id: string;
    patient_id: number;
    patient_name: string;
    day: string;
    time: string;
    status: string;
}

@Component({
    selector: 'app-doctor-home-appointments',
    standalone: true,
    imports: [HttpClientModule, NgIf, NgFor],
    templateUrl: './doctor-home-appointments.component.html',
    styleUrl: './doctor-home-appointments.component.scss'
})
export class DoctorHomeAppointmentsComponent implements OnInit {
    appointments: Appointment[] = [];
    isLoading: boolean = false;
    errorMessage: string | null = null;

    constructor(private http: HttpClient, private router: Router) {}

    ngOnInit(): void {
        this.fetchAppointments();
    }

    fetchAppointments(): void {
        this.isLoading = true;
        this.errorMessage = null;

        const token = localStorage.getItem('doctorToken'); 
        console.log('DoctorAppointmentsComponent - Token:', token);
        if (!token) {
            this.errorMessage = 'You are not authenticated. Please log in.';
            this.isLoading = false;
            this.router.navigate(['/doctor-login']);
            return;
        }

        const headers = new HttpHeaders({
            'Authorization': `Bearer ${token}`
        });

        this.http.get<Appointment[]>('http://localhost:8000/api/doctor/appointments', { headers }).subscribe(
            (response) => {
                console.log('DoctorAppointmentsComponent - Appointments Response:', response);
                if (Array.isArray(response)) {
                    this.appointments = response.map(appt => ({
                        ...appt,
                        time: this.convertTimeFormat(appt.time)
                    }));
                } else {
                    this.errorMessage = 'Invalid appointments data received.';
                    this.appointments = [];
                }
                this.isLoading = false;
            },
            (error) => {
                console.error('DoctorAppointmentsComponent - Error fetching appointments:', error);
                this.errorMessage = error.status === 401 ? 'Unauthorized access. Please log in.' : 'Failed to load appointments. Please try again later.';
                this.isLoading = false;
                if (error.status === 401) {
                    console.log('DoctorAppointmentsComponent - Unauthorized, redirecting to login...');
                    this.router.navigate(['/doctor-login']);
                }
            }
        );
    }

    convertTimeFormat(time: string): string {
        const [hour, minute] = time.split(':');
        let hourNum = parseInt(hour, 10);
        const period = hourNum >= 12 ? 'PM' : 'AM';
        hourNum = hourNum % 12 || 12;
        return `${hourNum}:${minute}${period}`;
    }

    getPatientName(day: string, time: string): string | null {
        const appointment = this.appointments.find(appt => appt.day === day && appt.time === time);
        return appointment ? appointment.patient_name : null;
    }

    hasAppointment(day: string, time: string): boolean {
        return this.appointments.some(appt => appt.day === day && appt.time === time);
    }
}
