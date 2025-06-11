import { ComponentFixture, TestBed } from '@angular/core/testing';

import { DoctorProfileConsultationComponent } from './doctor-profile-consultation.component';

describe('DoctorProfileConsultationComponent', () => {
  let component: DoctorProfileConsultationComponent;
  let fixture: ComponentFixture<DoctorProfileConsultationComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [DoctorProfileConsultationComponent]
    })
    .compileComponents();
    
    fixture = TestBed.createComponent(DoctorProfileConsultationComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
