@extends('layouts.app')

@section('header')
<div class="d-flex justify-content-between align-items-center">
    <h2 class="fw-semibold text-primary mb-0">
        <i class="fa-solid fa-calendar-days me-2"></i> Event Calendar
    </h2>
</div>
@endsection

@section('content')
<div class="row justify-content-center my-4">
    <div class="col-lg-10">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div id="calendar" class="border rounded p-2" style="min-height: 600px;"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <!-- FullCalendar -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.9/index.global.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendar');
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
                },
                themeSystem: 'bootstrap5',
                events: "{{ route('events.calendar.data') }}",
                eventClick: function(info) {
                    info.jsEvent.preventDefault();
                    if (info.event.url) {
                        window.open(info.event.url, '_self');
                    }
                },
                eventContent: function(arg) {
                    const location = arg.event.extendedProps.location;

                    const titleEl = document.createElement('div');
                    titleEl.classList.add('fw-semibold');
                    // XSS fix: user-controlled title goes in via a text node,
                    // never through innerHTML.
                    titleEl.innerHTML = '<i class="fa-solid fa-circle me-1 text-primary small"></i>';
                    titleEl.append(document.createTextNode(arg.event.title));

                    const locationEl = document.createElement('div');
                    locationEl.classList.add('small', 'text-muted', 'mt-1');
                    locationEl.innerHTML = '<i class="bi bi-geo-alt me-1 text-secondary"></i>';
                    locationEl.append(document.createTextNode(location || ''));

                    return { domNodes: [titleEl, locationEl] };
                }
            });
            calendar.render();
        });
    </script>

    <style>
        /* FullCalendar Bootstrap Overrides */
        .fc .fc-toolbar-title {
            font-weight: 600;
            color: #0d6efd; /* primary */
        }

        .fc-daygrid-event {
            border-radius: 0.5rem;
            padding: 4px 6px;
            transition: all 0.2s ease-in-out;
        }

        .fc-daygrid-event:hover {
            background-color: #e7f1ff; /* subtle hover */
            transform: translateY(-2px);
        }

        .fc .fc-button-primary {
            background-color: #0d6efd;
            border: none;
        }

        .fc .fc-button-primary:hover {
            background-color: #0b5ed7;
        }

        .fc-event-title {
            font-size: 0.9rem;
        }
    </style>
@endpush
