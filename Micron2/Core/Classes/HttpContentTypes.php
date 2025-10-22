<?php

enum HttpContentType: string
{
    // Testuali
    case TEXT_PLAIN       = 'text/plain';
    case TEXT_HTML        = 'text/html';
    case TEXT_CSS         = 'text/css';
    case TEXT_CSV         = 'text/csv';
    case TEXT_XML         = 'text/xml';

    // JSON / JavaScript
    case APPLICATION_JSON       = 'application/json';
    case APPLICATION_JAVASCRIPT = 'application/javascript';

    // Form
    case FORM_URLENCODED = 'application/x-www-form-urlencoded';
    case FORM_DATA        = 'multipart/form-data';

    // Binari / generici
    case OCTET_STREAM = 'application/octet-stream';
    case PDF          = 'application/pdf';
    case ZIP          = 'application/zip';

    // Immagini
    case IMAGE_PNG  = 'image/png';
    case IMAGE_JPEG = 'image/jpeg';
    case IMAGE_GIF  = 'image/gif';
    case IMAGE_SVG  = 'image/svg+xml';
    case IMAGE_WEBP = 'image/webp';

    // Audio / Video
    case AUDIO_MPEG  = 'audio/mpeg';
    case VIDEO_MP4   = 'video/mp4';
    case VIDEO_WEBM  = 'video/webm';

    // XML / SOAP
    case APPLICATION_XML       = 'application/xml';
    case APPLICATION_SOAP_XML  = 'application/soap+xml';

    // Font
    case FONT_WOFF  = 'font/woff';
    case FONT_WOFF2 = 'font/woff2';
}