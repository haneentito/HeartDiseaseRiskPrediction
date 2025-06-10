import { RouterModule, Routes } from '@angular/router';
import { LandingComponent } from './Landing/landing.component';
import { DoctorLoginComponent } from './doctor-login/doctor-login.component';
import { PatientLoginComponent } from './patient-login/patient-login.component';
import { PatientRegistrationComponent } from './patient-registration/patient-registration.component';
import { DoctorRegistrationComponent } from './doctor-registration/doctor-registration.component';
import { PatientHomeComponent } from './patient-home/patient-home.component';
import { BlogComponent } from './blog/blog.component';
import { ContactComponent } from './contact/contact.component';
import { PredictComponent } from './predict/predict.component';
import { PatientConsultationComponent } from './patient-consultation/patient-consultation.component';
import { DoctorProfileConsultationComponent } from './doctor-profile-consultation/doctor-profile-consultation.component';
import { PatientProfileInformationComponent } from './patient-profile-information/patient-profile-information.component';
import { DoctorHomeComponent } from './doctor-home/doctor-home.component';
import { DoctorHomeProfileComponent } from './doctor-home-profile/doctor-home-profile.component';
import { DoctorHomeAppointmentsComponent } from './doctor-home-appointments/doctor-home-appointments.component';
import { ForgetPasswordComponent } from './forget-password/forget-password.component';
import { ResetPasswordComponent } from './reset-password/reset-password.component';
import { PageNotFoundComponent } from './page-not-found/page-not-found.component';
import { DoctorGuard } from './auth.guard'; // Add this import

export const routes: Routes = [
  { path: '', redirectTo: 'landing', pathMatch: 'full' },
  { path: 'landing', component: LandingComponent, title: 'Landing' },
  { path: 'doctor-login', component: DoctorLoginComponent, title: 'Doctor Login' },
  { path: 'patient-login', component: PatientLoginComponent, title: 'Patient Login' },
  { path: 'doctor-registration', component: DoctorRegistrationComponent, title: 'Doctor Registration' },
  { path: 'patient-registration', component: PatientRegistrationComponent, title: 'Patient Registration' },
  { path: 'patient-home', component: PatientHomeComponent, title: 'Patient Home' },
  { path: 'contact', component: ContactComponent },
  { path: 'blog', component: BlogComponent, title: 'Blog' },
  { path: 'predict', component: PredictComponent, title: 'Prediction' },
  { path: 'patient-consultation', component: PatientConsultationComponent, title: 'Consultation' },
  { path: 'doctor-profile-consultation/:id', component: DoctorProfileConsultationComponent, title: 'Doctor Profile' },
  { path: 'patient-profile-information', component: PatientProfileInformationComponent, title: 'Profile' },
  { path: 'doctor-home', component: DoctorHomeComponent, title: 'Doctor Home', canActivate: [DoctorGuard] }, // Add guard
  { path: 'doctor-home-profile', component: DoctorHomeProfileComponent, title: 'Doctor Profile', canActivate: [DoctorGuard] },
  { path: 'doctor-home-appointments', component: DoctorHomeAppointmentsComponent, title: 'Doctor Appointments', canActivate: [DoctorGuard] },
  { path: 'forget-password', component: ForgetPasswordComponent, title: 'Forgot Password' },
  { path: 'reset-password', component: ResetPasswordComponent, title: 'Reset Password' },
  { path: 'logout', redirectTo: 'patient-login', pathMatch: 'full' },
  { path: '**', component: PageNotFoundComponent, title: 'Not Found' }
];

export class AppRoutingModule {}