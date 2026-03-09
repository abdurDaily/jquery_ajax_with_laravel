// resources/js/app.js

import './bootstrap'; // Laravel default bootstrap file
import $ from 'jquery'; // Import jQuery
window.$ = window.jQuery = $; // Make jQuery global

import Swal from 'sweetalert2'; // Import SweetAlert2
window.Swal = Swal; // Make Swal global