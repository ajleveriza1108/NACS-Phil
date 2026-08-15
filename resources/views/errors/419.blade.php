@extends('errors.layout')

@section('title', 'Session Expired')
@section('code', '419')
@section('heading', 'Your secure session expired.')
@section('message', 'For your protection, the form session is no longer valid. Return to the page and submit it again.')
@section('help', 'Do not use the browser Back button to resubmit sensitive information after a long delay.')
