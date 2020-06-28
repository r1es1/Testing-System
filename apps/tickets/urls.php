<?php

return array(
    url('/', 'main', 'main'),
    url('/tickets/take/{test_number}/{variant_number}', 'take', 'take'),
    url('/tickets/view/{test_number}/{variant_number}', 'view', 'view'),
    url('/tickets/check/{test_number}/{variant_number}', 'check', 'check'),
);