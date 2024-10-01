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
        events: '/api/coach/classes',
        
        eventClick: function(info) {
            // Mengisi modal dengan informasi kelas yang dipilih
            document.getElementById('classTitle').innerText = info.event.title;
            document.getElementById('classTime').innerText = 'Time: ' + info.event.start.toLocaleString() + ' - ' + info.event.end.toLocaleString();
            document.getElementById('classQuota').innerText = 'Quota: ' + info.event.extendedProps.quota;

            // Menampilkan modal
            $('#classDetailModal').modal('show');
        }
    });
    calendar.render();
});

