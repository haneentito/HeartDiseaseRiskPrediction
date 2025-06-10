import { bootstrapApplication } from '@angular/platform-browser';
import { AppComponent } from './app/app.component';
import { provideRouter } from '@angular/router';
import { routes } from './app/app.routes';
import { provideHttpClient } from '@angular/common/http';

// Bootstrap AppComponent with routing and HTTP client providers
bootstrapApplication(AppComponent, {
  providers: [
    provideRouter(routes), // Provide the router with defined routes
    provideHttpClient() // Provide HttpClient for API calls
  ]
}).catch(err => console.error(err));