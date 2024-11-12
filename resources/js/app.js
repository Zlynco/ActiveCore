import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import axios from 'axios';

document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');

    if (calendarEl) {
        var calendar = new Calendar(calendarEl, {
            plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            events: async function (fetchInfo, successCallback, failureCallback) {
                try {
                    // Ambil event kelas
                    const classResponse = await axios.get('/api/coach/classes');
                    const classEvents = classResponse.data.map(event => ({
                        title: event.name,
                        start: event.start_time,
                        end: event.end_time,
                        extendedProps: {
                            category: event.category_id,
                            registered_count: event.registered_count,
                        }
                    }));

                    // Ambil event booking coach
                    const bookingResponse = await axios.get('/api/coach/coach-bookings');
                    const bookingEvents = bookingResponse.data.map(booking => ({
                        title: booking.title,
                        start: booking.start,
                        end: booking.end,
                        extendedProps: {
                            coach: booking.extendedProps.coach,
                            member: booking.extendedProps.member,
                            session_count: booking.extendedProps.session_count,
                        }
                    }));

                    const attendanceResponse = await axios.get('/api/coach/coach-attendance');
console.log('Attendance Data:', attendanceResponse.data); // Log data untuk debugging
const attendanceEvents = attendanceResponse.data.map(attendance => ({
    title: attendance.title,
    start: attendance.start,
    end: attendance.end,
    color: attendance.color,
    extendedProps: {
        status: attendance.extendedProps.status,
        reason: attendance.extendedProps.reason,
    }
}));


                    // Gabungkan semua events
                    const allEvents = [...classEvents, ...bookingEvents, ...attendanceEvents];

                    successCallback(allEvents);
                } catch (error) {
                    console.error('Error fetching events:', error);
                    failureCallback(error);
                }
            },
            eventClick: function (info) {
                $('#classDetailModal').modal('show');
                var modalContent = $('#classDetailModal .modal-content');
                modalContent.html(`
                    <div class="modal-header">
                        <h5 class="modal-title">${info.event.title}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p><strong>Start:</strong> ${info.event.start.toLocaleDateString()} ${info.event.start.toLocaleTimeString()}</p>
                        <p><strong>End:</strong> ${info.event.end ? info.event.end.toLocaleTimeString() : ''}</p>
                        ${info.event.extendedProps.category ? `<p><strong>Category:</strong> ${info.event.extendedProps.category}</p>` : ''}
                        ${info.event.extendedProps.registered_count ? `<p><strong>Registrant:</strong> ${info.event.extendedProps.registered_count}</p>` : ''}
                        ${info.event.extendedProps.member ? `<p><strong>Member:</strong> ${info.event.extendedProps.member}</p>` : ''}
                        ${info.event.extendedProps.session_count ? `<p><strong>Session Count:</strong> ${info.event.extendedProps.session_count}</p>` : ''}
                        ${info.event.extendedProps.status ? `<p><strong>Status:</strong> ${info.event.extendedProps.status}</p>` : ''}
                        ${info.event.extendedProps.reason ? `<p><strong>Reason:</strong> ${info.event.extendedProps.reason}</p>` : ''}
                    </div>
                `);
            }
        });

        calendar.render();
    }
});
