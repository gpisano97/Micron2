<?php

enum HttpHeader: string
{
    // Generali
    case ACCEPT             = 'Accept';
    case ACCEPT_ENCODING    = 'Accept-Encoding';
    case ACCEPT_LANGUAGE    = 'Accept-Language';
    case CACHE_CONTROL      = 'Cache-Control';
    case CONNECTION         = 'Connection';
    case CONTENT_LENGTH     = 'Content-Length';
    case CONTENT_TYPE       = 'Content-Type';
    case DATE               = 'Date';
    case HOST               = 'Host';
    case PRAGMA             = 'Pragma';
    case TRAILER            = 'Trailer';
    case TRANSFER_ENCODING  = 'Transfer-Encoding';
    case UPGRADE            = 'Upgrade';
    case VIA                = 'Via';

    // Autenticazione
    case AUTHORIZATION      = 'Authorization';
    case WWW_AUTHENTICATE   = 'WWW-Authenticate';
    case PROXY_AUTHENTICATE = 'Proxy-Authenticate';
    case PROXY_AUTHORIZATION = 'Proxy-Authorization';

    // CORS / sicurezza
    case ORIGIN             = 'Origin';
    case ACCESS_CONTROL_ALLOW_ORIGIN = 'Access-Control-Allow-Origin';
    case ACCESS_CONTROL_ALLOW_METHODS = 'Access-Control-Allow-Methods';
    case ACCESS_CONTROL_ALLOW_HEADERS = 'Access-Control-Allow-Headers';
    case ACCESS_CONTROL_REQUEST_METHOD = 'Access-Control-Request-Method';
    case ACCESS_CONTROL_REQUEST_HEADERS = 'Access-Control-Request-Headers';
    case STRICT_TRANSPORT_SECURITY = 'Strict-Transport-Security';
    case X_FRAME_OPTIONS    = 'X-Frame-Options';
    case X_CONTENT_TYPE_OPTIONS = 'X-Content-Type-Options';
    case CONTENT_SECURITY_POLICY = 'Content-Security-Policy';

    // Client / Server info
    case USER_AGENT         = 'User-Agent';
    case REFERER            = 'Referer';
    case SERVER             = 'Server';
    case X_POWERED_BY       = 'X-Powered-By';

    // Cookie
    case COOKIE             = 'Cookie';
    case SET_COOKIE         = 'Set-Cookie';

    // Redirezione / Location
    case LOCATION           = 'Location';
}