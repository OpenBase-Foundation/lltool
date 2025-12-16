<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>LLTool — PHP</title>
  <style>body{font-family:Arial,Helvetica,sans-serif;margin:0;padding:0;background:#f3f4f6}header{background:#111827;color:#fff;padding:1rem}main{padding:1rem} .container{max-width:900px;margin:0 auto}</style>
</head>
<body>
<header>
  <div class="container">
    <h1>LLTool — PHP</h1>
    <nav><a href="/?page=home" style="color:#fff;margin-right:8px">Home</a><a href="/?page=cohorts" style="color:#fff;margin-right:8px">Cohorts</a><?php if (isset($_SESSION['user_id'])): ?><a href="/?page=admin&action=dashboard" style="color:#fff;margin-right:8px">Admin</a><?php endif; ?><a href="/?page=logout" style="color:#fff">Logout</a></nav>
  </div>
</header>
<main>
  <div class="container">
