import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import timeGridPlugin from '@fullcalendar/timegrid';


document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new Calendar(calendarEl, {
        plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
        initialView: 'dayGridMonth',
        // Mengambil events dari dua API secara bersamaan
        events: async function() {
            try {
                const classesResponse = await fetch('/api/coach/classes');
                const classes = await classesResponse.json();
                console.log('Classes:', classes); // Log kelas

                const bookingsResponse = await fetch('/api/coach/coach-bookings');
                const bookings = await bookingsResponse.json();
                console.log('Bookings:', bookings); // Log booking
                
                // Menggabungkan kelas dan booking menjadi satu array events
                return [...classes, ...bookings];
            } catch (error) {
                console.error('Error fetching events:', error);
                return []; // Kembali array kosong jika ada kesalahan
            }
        },
        
        eventClick: function(info) {
            // Mengisi modal dengan informasi kelas yang dipilih
            const startTime = info.event.start.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            const endTime = info.event.end.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

            document.getElementById('classTitle').innerText = info.event.title;
            document.getElementById('classTime').innerText = `Time: ${startTime} - ${endTime}`;
            document.getElementById('classQuota').innerText = `Quota: ${info.event.extendedProps.quota}`;

            // Menampilkan modal
            $('#classDetailModal').modal('show');
        }
    });
    calendar.render();
});

