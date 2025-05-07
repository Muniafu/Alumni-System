<div>
    <div wire:ignore class="my-4">
        <div id="calendar"></div>
    </div>

    @push('scripts')
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
                    events: @json($this->events),
                    eventClick: function(info) {
                        info.jsEvent.preventDefault();
                        if (info.event.url) {
                            window.open(info.event.url, '_blank');
                        }
                    },
                    editable: false,
                    selectable: true,
                    selectMirror: true,
                    dayMaxEvents: true,
                });
                calendar.render();
            });
        </script>
    @endpush
</div>