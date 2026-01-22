import './bootstrap';

import Alpine from 'alpinejs';
import timer from './timer';
import timeEntries from './timeEntries';

window.Alpine = Alpine;

Alpine.data('timer', timer);
Alpine.data('timeEntries', timeEntries);

Alpine.start();
