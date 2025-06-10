import { Component, OnInit } from '@angular/core';
import { HttpClient, HttpErrorResponse } from '@angular/common/http';
import { HttpClientModule } from '@angular/common/http'; // Added to fix NullInjectorError
import { NgIf, NgClass } from '@angular/common';
import { FormsModule, ReactiveFormsModule, FormGroup, FormBuilder, Validators } from '@angular/forms';

@Component({
  selector: 'app-predict',
  standalone: true,
  imports: [FormsModule, HttpClientModule, ReactiveFormsModule, NgIf, NgClass], // Added HttpClientModule
  templateUrl: './predict.component.html',
  styleUrls: ['./predict.component.scss']
})
export class PredictComponent implements OnInit {
  predictForm: FormGroup;
  predictionResult: string | null = null;
  predictionClass: string = '';

  constructor(private fb: FormBuilder, private http: HttpClient) {
    this.predictForm = this.fb.group({
      age: [null, [Validators.required, Validators.min(0)]],
      sex: [null, Validators.required],
      chestPainType: [null, Validators.required],
      restingBP: [null, [Validators.required, Validators.min(0)]],
      cholestoral: [null, [Validators.required, Validators.min(0)]],
      fastingBS: [null, Validators.required],
      restingECG: [null, Validators.required],
      maxHeartRate: [null, [Validators.required, Validators.min(0)]],
      exerciseAngina: [null, Validators.required],
      oldpeak: [null, [Validators.required, Validators.min(0)]],
      slope: [null, Validators.required],
      numVessels: [null, Validators.required],
      thal: [null, Validators.required]
    });
  }

  ngOnInit(): void {}

  preprocessData(formData: any): any {
    return {
      age: formData.age > 45 ? 1 : 0,
      gender: parseInt(formData.sex),
      chest_pain_type: formData.chestPainType === '0' ? 1 :
                       formData.chestPainType === '1' ? 2 :
                       formData.chestPainType === '2' ? 3 : 0,
      fasting_bs: parseInt(formData.fastingBS),
      rest_ecg: parseInt(formData.restingECG),
      exercise_angina: parseInt(formData.exerciseAngina),
      ex_slope: formData.slope === '0' ? 2 :
                formData.slope === '1' ? 1 : 0,
      num_major_vessels: parseInt(formData.numVessels),
      thal: parseInt(formData.thal),
      cholesterol: formData.cholestoral < 200 ? 0 :
                   formData.cholestoral <= 239 ? 1 : 2,
      heart_rate: formData.maxHeartRate < 100 ? 1 : 0, // Original logic
      model: 'log'
    };
  }

  predictHeartDisease(): void {
    if (this.predictForm.invalid) {
      alert('Please fill all fields with valid data.');
      this.predictForm.markAllAsTouched();
      return;
    }

    const preprocessedData = this.preprocessData(this.predictForm.value);
    this.sendToBackend(preprocessedData);
  }

  sendToBackend(data: any): void {
    const apiUrl = 'http://localhost:8000/api/predict';
    this.http.post<any>(apiUrl, data, { headers: { 'Content-Type': 'application/json' } }).subscribe(
      (response) => {
        this.displayResult(response.prediction);
      },
      (error: HttpErrorResponse) => {
        console.error('Error:', error);
        alert('An error occurred while predicting. Please try again.');
      }
    );
  }

  displayResult(result: number): void {
    this.predictionResult = result === 1 ? 'Positive for Heart Disease' : 'Negative for Heart Disease';
    this.predictionClass = result === 1 ? 'text-red-400' : 'text-green-400';
  }

  isInvalid(controlName: string): boolean {
    const control = this.predictForm.get(controlName);
    return control ? (control.invalid && (control.touched || control.dirty)) : false;
  }
}