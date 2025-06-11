import { Component } from '@angular/core';
import { HttpClient, HttpClientModule } from '@angular/common/http';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { ReactiveFormsModule } from '@angular/forms';

@Component({
  selector: 'app-contact',
  templateUrl: './contact.component.html',
  styleUrls: ['./contact.component.scss'],
  standalone: true,
  imports: [ReactiveFormsModule, HttpClientModule]
})
export class ContactComponent {
  contactForm: FormGroup;

  constructor(private fb: FormBuilder, private http: HttpClient) {
    this.contactForm = this.fb.group({
      name: ['', Validators.required],
      email: ['', [Validators.required, Validators.email]],
      message: ['', Validators.required],
    });
  }

  onSubmit() {
    if (this.contactForm.valid) {
      this.http.post('http://localhost:8000/api/contact', this.contactForm.value).subscribe(
        () => alert('Message sent successfully!'),
        (error) => {
          console.error('Error sending message:', error);
          alert('Failed to send message. Please try again or check the console for details.');
        }
      );
    }
  }
}