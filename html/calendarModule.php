<?php
session_start();

if(isset($_SESSION['id'])){
    $userId = $_SESSION['id'];
} else {
    
}

?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8" />
  <title>My Calendar</title>
  <link
    href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css"
    rel="stylesheet"
    />
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
  <link rel="stylesheet" type="text/css" href="../css/calendar.css">

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
        events: 'includes/get_activitiesJoiner.php',
        selectable: false, 
        editable: false,
        dayMaxEvents: true,

        // ← add this:
        eventClick: function(info) {
          // info.event.id is the id from your JSON
          const eventId = info.event.id;
          // navigate to your show-details page, e.g. show_event.php?id=123
        }
      });
      calendar.render();
    });
  </script>
</body>
</html>
