 <!-- Footer -->
 <div class="footer">
     <p>&copy; 2023 PowerFit Gym. Sistema de Gestión. Todos los derechos reservados.</p>
 </div>
 </div>
 </div>


 <script>
     // Toggle sidebar en móviles
     document.querySelector('.toggle-sidebar').addEventListener('click', function() {
         document.querySelector('.sidebar').classList.toggle('active');
         document.querySelector('.main-content').classList.toggle('active');
     });

     // Gráfico de asistencia
     const chartCanvas = document.getElementById('attendanceChart');
     if (chartCanvas) {
         const ctx = chartCanvas.getContext('2d');
         const attendanceChart = new Chart(ctx, {
             type: 'line',
             data: {
                 labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
                 datasets: [{
                     label: 'Asistencia Mensual',
                     data: [850, 920, 1000, 1100, 1200, 1250, 1300, 1280, 1350, 1400, 1450, 1500],
                     backgroundColor: 'rgba(231, 76, 60, 0.1)',
                     borderColor: 'rgba(231, 76, 60, 1)',
                     borderWidth: 2,
                     tension: 0.4,
                     fill: true
                 }]
             },
             options: {
                 responsive: true,
                 maintainAspectRatio: false,
                 plugins: {
                     legend: {
                         display: false
                     }
                 },
                 scales: {
                     y: {
                         beginAtZero: true,
                         grid: {
                             drawBorder: false
                         }
                     },
                     x: {
                         grid: {
                             display: false
                         }
                     }
                 }
             }
         });
     }

     // Simular notificaciones
     document.querySelector('.notification-bell').addEventListener('click', function() {
         alert('Tienes 3 notificaciones:\n- Nuevo mensaje de Carlos\n- Pago pendiente de Ana\n- Clase de yoga llena');
     });
 </script>
 <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
 </body>

 </html>