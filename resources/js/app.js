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
        
        events: async function() {
            return await fetchEvents();
        },

        // Klik tanggal di kalender
        dateClick: async function(info) {
            const clickedDate = new Date(info.dateStr); // Tanggal yang diklik
            const today = new Date(); // Tanggal hari ini
            today.setHours(0, 0, 0, 0); // Set jam ke awal hari untuk perbandingan
            clickedDate.setHours(0, 0, 0, 0); // Set jam ke awal hari untuk perbandingan

            try {
                const allEvents = await fetchEvents();
                const clickedDateEvents = allEvents.filter(event => {
                    const eventStart = new Date(event.start);
                    return eventStart.toDateString() === clickedDate.toDateString();
                });

                let details = '';
                if (clickedDateEvents.length > 0) {
                    clickedDateEvents.forEach(event => {
                        const startTime = new Date(event.start).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                        const endTime = new Date(event.end).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                        details += `<p>${event.title}: ${startTime} - ${endTime}</p>`;
                    });
                    document.getElementById('classTitle').innerHTML = (clickedDate.getTime() === today.getTime()) ? 'Today\'s Events' : `Events on ${info.dateStr}`;
                } else {
                    document.getElementById('classTitle').innerText = (clickedDate.getTime() === today.getTime()) ? 'No Events Today' : `No Events on ${info.dateStr}`;
                }
                document.getElementById('classTime').innerHTML = details;

                // Tampilkan modal detail
                $('#classDetailModal').modal('show');

            } catch (error) {
                console.error('Error fetching events:', error);
            }
        },

        // Klik event
        eventClick: function(info) {
            const startTime = info.event.start.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            const endTime = info.event.end.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            document.getElementById('classTitle').innerText = info.event.title;
            document.getElementById('classTime').innerText = `Time: ${startTime} - ${endTime}`;
            document.getElementById('classQuota').innerText = info.event.extendedProps.quota ? `Quota: ${info.event.extendedProps.quota}` : `Session: ${info.event.extendedProps.session_count || 0}`;
            $('#classDetailModal').modal('show');
        },

        eventDidMount: function(info) {
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const eventStart = new Date(info.event.start);
            eventStart.setHours(0, 0, 0, 0);
            if (eventStart.getTime() === today.getTime()) {
                info.el.classList.add('today-event');
            }
        }
    });

    calendar.render();
});

// Fungsi untuk mengambil events dari API dan menangani pengulangan
async function fetchEvents() {
    try {
        const classesResponse = await fetch('/api/coach/classes');
        const classes = await classesResponse.json();
        const bookingsResponse = await fetch('/api/coach/coach-bookings');
        const bookings = await bookingsResponse.json();
        const allEvents = [...classes, ...bookings];

        // Mengolah event yang memiliki pengulangan
        const processedEvents = allEvents.flatMap(event => {
            if (event.recurrence && event.recurrence === 'weekly') {
                return getRecurringEvents(event); // Fungsi untuk mendapatkan event berulang
            }
            return [event]; // Kembalikan event jika bukan berulang
        });

        return processedEvents;
    } catch (error) {
        console.error('Error fetching events:', error);
        return [];
    }
}

// Fungsi untuk menghasilkan event yang berulang
function getRecurringEvents(event) {
    const recurringEvents = [];
    const today = new Date();
    const startDate = new Date(event.start);
    const dayOfWeek = startDate.getDay(); // Mengambil hari dari tanggal event awal

    // Tentukan tanggal hari ini
    today.setHours(0, 0, 0, 0);

    // Temukan semua hari dalam rentang waktu yang relevan
    while (startDate <= today) {
        if (startDate.getDay() === dayOfWeek) {
            recurringEvents.push({
                title: event.title,
                start: new Date(startDate),
                end: new Date(new Date(startDate).setHours(event.end.getHours(), event.end.getMinutes())) // Menggunakan waktu akhir dari event asli
            });
        }
        // Tambahkan 1 minggu
        startDate.setDate(startDate.getDate() + 7);
    }

    return recurringEvents;
}
