import { ComponentFixture, TestBed } from '@angular/core/testing';

import { PatientProfileInformationComponent } from './patient-profile-information.component';

describe('PatientProfileInformationComponent', () => {
  let component: PatientProfileInformationComponent;
  let fixture: ComponentFixture<PatientProfileInformationComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [PatientProfileInformationComponent]
    })
    .compileComponents();
    
    fixture = TestBed.createComponent(PatientProfileInformationComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
