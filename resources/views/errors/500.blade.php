@extends('errors.layout')

@section('title', 'Service Error')
@section('code', '500')
@section('heading', 'The service could not complete that request.')
@section('message', 'No private error details are shown here. Please try again later or return to the public website.')
@section('help', 'NACS-Phil administrators should review private server logs and monitoring rather than displaying technical details publicly.')
