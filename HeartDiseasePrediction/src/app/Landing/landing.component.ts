import { Component } from '@angular/core';
import { RouterLink, RouterOutlet } from '@angular/router';
import { Router } from '@angular/router';

@Component({
  selector: 'app-landing',
  standalone: true,
  imports: [RouterLink],
  templateUrl: './landing.component.html',
  styleUrl: './landing.component.scss'
})
export class LandingComponent {
  constructor(private router: Router) {}

  onDoctorLoginClick() {
    console.log('Doctor login button clicked, navigating to /doctor-login');
    this.router.navigate(['/doctor-login']);
  }

  onPatientLoginClick() {
    console.log('Patient login button clicked, navigating to /patient-login');
    this.router.navigate(['/patient-login']);
  }
}
