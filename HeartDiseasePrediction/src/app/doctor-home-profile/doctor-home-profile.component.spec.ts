import { ComponentFixture, TestBed } from '@angular/core/testing';

import { DoctorHomeProfileComponent } from './doctor-home-profile.component';

describe('DoctorHomeProfileComponent', () => {
  let component: DoctorHomeProfileComponent;
  let fixture: ComponentFixture<DoctorHomeProfileComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [DoctorHomeProfileComponent]
    })
    .compileComponents();
    
    fixture = TestBed.createComponent(DoctorHomeProfileComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
