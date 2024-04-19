<?php

return [
  'client' => [
    'profile' => 'aws',
    'version' => 'latest',
    'region' => 'us-west-002',
    'endpoint' => 'https://s3.us-west-002.example.com'
  ],
  'bucket_name' => 'bucket-name',
  'folder' => 'images', // key prefix, if filename is filename.png, the key become images/filename.png
  'acl' => 'public-read' // object ACL
];
