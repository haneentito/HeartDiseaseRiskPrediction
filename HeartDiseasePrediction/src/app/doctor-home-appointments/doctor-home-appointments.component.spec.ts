import { ComponentFixture, TestBed } from '@angular/core/testing';

import { DoctorHomeAppointmentsComponent } from './doctor-home-appointments.component';

describe('DoctorHomeAppointmentsComponent', () => {
  let component: DoctorHomeAppointmentsComponent;
  let fixture: ComponentFixture<DoctorHomeAppointmentsComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [DoctorHomeAppointmentsComponent]
    })
    .compileComponents();
    
    fixture = TestBed.createComponent(DoctorHomeAppointmentsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
