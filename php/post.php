<?php
	$data = array( 'files' => $_FILES, 'post' => $_POST);
	echo json_encode($data);
