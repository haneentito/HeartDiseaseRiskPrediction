import { Component } from '@angular/core';
import { RouterModule, RouterOutlet } from '@angular/router';
import { routes } from './app.routes'; // Import routes

@Component({
  selector: 'app-root',
  standalone: true,
  imports: [RouterOutlet, RouterModule],
  templateUrl: './app.component.html',
  styleUrl: './app.component.scss'
})
export class AppComponent {
  title = 'HeartDiseasePrediction';
}

// Configure routes globally
export const appConfig = {
  providers: [
    { provide: RouterModule, useValue: RouterModule.forRoot(routes) }
  ]
};
