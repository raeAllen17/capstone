<?php
session_start();
if (!isset($_SESSION['id'])) {
    header('Location: landing_page.php');
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8" />
  <title>My Calendar</title>
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
  <style>
    #calendar {
      max-width: 100%;
      max-height: 100vh;
      padding: 10px;
      margin: 0 auto;
    }
  </style>
</head>
<body style="margin: 0; padding: 0;">
  <div id="calendar"></div>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const calendarEl = document.getElementById('calendar');

      const calendar = new FullCalendar.Calendar(calendarEl, {
        headerToolbar: {
          left: 'prev,next today',
          center: 'title',
          right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        initialView: 'dayGridMonth',
        events: 'includes/get_activities.php',
        selectable: false,
        editable: false,
        dayMaxEvents: true,

        eventClick: function(info) {
          if (info.event.extendedProps.clickable === false) {
            info.jsEvent.preventDefault();
            return;
          }

          const eventId = info.event.id;
          window.location.href = 'org_manageParticipant.php?id=' + encodeURIComponent(eventId);
        }
      });

      calendar.render();
    });
  </script>
</body>
</html>
