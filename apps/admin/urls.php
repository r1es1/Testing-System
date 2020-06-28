<?php

return array(

    url('/admin', 'main', 'main'),
    url('/admin/tickets', 'tickets', 'tickets'),

    url('/admin/answers', 'answers', 'answers'),
    	url('/admin/answers/download/{id}', 'answers_download', 'answers_download'),
);