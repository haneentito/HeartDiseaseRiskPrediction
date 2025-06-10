import { Component } from '@angular/core';
import { RouterLink } from '@angular/router';

@Component({
  selector: 'app-patient-home',
  standalone: true,
  imports: [RouterLink],
  templateUrl: './patient-home.component.html',
  styleUrl: './patient-home.component.scss'
})
export class PatientHomeComponent {
  constructor() {
    
  }
}
