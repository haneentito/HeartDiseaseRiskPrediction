import { Injectable } from '@angular/core';
import { CanActivate, Router } from '@angular/router';

@Injectable({
  providedIn: 'root'
})
export class DoctorGuard implements CanActivate {
  constructor(private router: Router) {}

  canActivate(): boolean {
    const token = localStorage.getItem('doctorToken');
    const userRole = localStorage.getItem('user_role');
    if (token && userRole === 'doctor') {
      return true;
    }
    this.router.navigate(['/doctor-login']);
    return false;
  }
}