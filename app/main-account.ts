import {bootstrap}  from 'angular2/platform/browser';
import {accountMod} from './account';
import {HTTP_PROVIDERS}  from 'angular2/http';
bootstrap(accountMod,[HTTP_PROVIDERS]);
