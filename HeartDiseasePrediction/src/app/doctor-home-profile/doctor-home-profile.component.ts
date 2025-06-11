import { Component, OnInit } from '@angular/core';
import { HttpClient, HttpClientModule, HttpHeaders } from '@angular/common/http';
import { NgIf, NgFor } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Router } from '@angular/router';
import { CommonModule } from '@angular/common';
import { UniqueTimesPipe } from '../pipes/unique-times.pipe';

interface Doctor {
    id: number;
    doctor_id: string;
    name: string;
    email: string;
    phone: string;
    role: string;
    rating: number;
    views: number;
}

interface ScheduleSlot {
    id?: number;
    day: string;
    time: string;
}

@Component({
    selector: 'app-doctor-home-profile',
    standalone: true,
    imports: [HttpClientModule, NgIf, NgFor, FormsModule, CommonModule, UniqueTimesPipe],
    templateUrl: './doctor-home-profile.component.html',
    styleUrls: ['./doctor-home-profile.component.scss']
})
export class DoctorHomeProfileComponent implements OnInit {
    doctor: Doctor | null = null;
    schedule: ScheduleSlot[] = [];
    isLoadingDoctor: boolean = false;
    isLoadingSchedule: boolean = false;
    errorMessage: string | null = null;
    newSlotDay: string = '';
    newSlotTime: string = '';
    editingSlot: ScheduleSlot | null = null;

    constructor(private http: HttpClient, private router: Router) {}

    ngOnInit(): void {
        this.fetchDoctorProfile();
        this.fetchSchedule();
    }

    fetchDoctorProfile(): void {
        this.isLoadingDoctor = true;
        this.errorMessage = null;

        const token = localStorage.getItem('doctorToken');
        console.log('DoctorHomeComponent - Token:', token);
        if (!token) {
            this.errorMessage = 'You are not authenticated. Please log in.';
            this.isLoadingDoctor = false;
            this.router.navigate(['/doctor-login']);
            return;
        }

        const headers = new HttpHeaders({
            'Authorization': `Bearer ${token}`
        });

        this.http.get<Doctor>('http://localhost:8000/api/doctor/profile', { headers }).subscribe(
            (response) => {
                console.log('DoctorHomeComponent - Profile Response:', response);
                if (response && typeof response === 'object') {
                    this.doctor = response;
                } else {
                    this.errorMessage = 'Invalid profile data received.';
                }
                this.isLoadingDoctor = false;
            },
            (error) => {
                console.error('DoctorHomeComponent - Error fetching doctor profile:', error);
                this.errorMessage = error.status === 401 ? 'Unauthorized access. Please log in.' : 'Failed to load profile. Please try again later.';
                this.isLoadingDoctor = false;
                if (error.status === 401) {
                    this.router.navigate(['/doctor-login']);
                }
            }
        );
    }

    fetchSchedule(): void {
        this.isLoadingSchedule = true;
        this.errorMessage = null;

        const token = localStorage.getItem('doctorToken');
        console.log('DoctorHomeComponent - Token for schedule:', token);
        if (!token) {
            this.errorMessage = 'You are not authenticated. Please log in.';
            this.isLoadingSchedule = false;
            this.router.navigate(['/doctor-login']);
            return;
        }

        const headers = new HttpHeaders({
            'Authorization': `Bearer ${token}`
        });

        this.http.get<ScheduleSlot[]>('http://localhost:8000/api/doctor/schedule', { headers }).subscribe(
            (response) => {
                console.log('DoctorHomeComponent - Raw Schedule Response:', response);
                if (Array.isArray(response)) {
                    console.log('DoctorHomeComponent - Parsed Schedule:', response);
                    this.schedule = [...response];
                } else {
                    this.errorMessage = 'Invalid schedule data received.';
                    this.schedule = [];
                }
                this.isLoadingSchedule = false;
            },
            (error) => {
                console.error('DoctorHomeComponent - Error fetching schedule:', error);
                this.errorMessage = error.status === 401 ? 'Unauthorized access. Please log in.' : 'Failed to load schedule. Please try again later.';
                this.isLoadingSchedule = false;
                if (error.status === 401) {
                    this.router.navigate(['/doctor-login']);
                }
            }
        );
    }

    createSlot(): void {
        if (!this.newSlotDay || !this.newSlotTime) {
            alert('Please select a day and time.');
            return;
        }

        const token = localStorage.getItem('doctorToken');
        if (!token) {
            this.router.navigate(['/doctor-login']);
            return;
        }

        const headers = new HttpHeaders({
            'Authorization': `Bearer ${token}`
        });

        const slot = { day: this.newSlotDay, time: this.newSlotTime };
        this.http.post('http://localhost:8000/api/doctor/schedule', slot, { headers }).subscribe(
            () => {
                this.fetchSchedule();
                this.newSlotDay = '';
                this.newSlotTime = '';
                alert('Slot created successfully!');
            },
            (error) => {
                console.error('DoctorHomeComponent - Error creating slot:', error);
                if (error.status === 401) {
                    this.router.navigate(['/doctor-login']);
                } else if (error.status === 422) {
                    alert('Failed to create slot due to validation errors. Please check the day and time.');
                } else {
                    alert('Failed to create slot. Please try again.');
                }
            }
        );
    }

    editSlot(slot: ScheduleSlot): void {
        this.editingSlot = { ...slot };
    }

    saveEditedSlot(): void {
        if (!this.editingSlot || !this.editingSlot.day || !this.editingSlot.time || !this.editingSlot.id) {
            alert('Invalid slot data.');
            return;
        }

        const token = localStorage.getItem('doctorToken');
        if (!token) {
            this.router.navigate(['/doctor-login']);
            return;
        }

        const headers = new HttpHeaders({
            'Authorization': `Bearer ${token}`
        });

        const slot = { day: this.editingSlot.day, time: this.editingSlot.time };
        this.http.put(`http://localhost:8000/api/doctor/schedule/${this.editingSlot.id}`, slot, { headers }).subscribe(
            () => {
                this.fetchSchedule();
                this.editingSlot = null;
                alert('Slot updated successfully!');
            },
            (error) => {
                console.error('DoctorHomeComponent - Error updating slot:', error);
                if (error.status === 401) {
                    this.router.navigate(['/doctor-login']);
                } else if (error.status === 422) {
                    alert('Failed to update slot due to validation errors. Please check the day and time.');
                } else {
                    alert('Failed to update slot. Please try again.');
                }
            }
        );
    }

    cancelEdit(): void {
        this.editingSlot = null;
    }

    deleteSlot(slotId: number | undefined): void {
        if (!slotId) {
            alert('Invalid slot ID.');
            return;
        }

        const token = localStorage.getItem('doctorToken');
        if (!token) {
            this.router.navigate(['/doctor-login']);
            return;
        }

        const headers = new HttpHeaders({
            'Authorization': `Bearer ${token}`
        });

        this.http.delete(`http://localhost:8000/api/doctor/schedule/${slotId}`, { headers }).subscribe(
            () => {
                this.fetchSchedule();
                alert('Slot deleted successfully!');
            },
            (error) => {
                console.error('DoctorHomeComponent - Error deleting slot:', error);
                if (error.status === 401) {
                    this.router.navigate(['/doctor-login']);
                } else {
                    alert('Failed to delete slot. Please try again.');
                }
            }
        );
    }

    hasSlot(day: string, time: string): boolean {
        return this.schedule.some(slot => slot.day === day && slot.time === time);
    }
}
