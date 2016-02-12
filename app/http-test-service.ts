import {Injectable} from 'angular2/core';
import {Http} from 'angular2/http';
import 'rxjs/add/operator/map'
import {Headers} from 'angular2/http';

@Injectable()
export class HTTPTestService {
   constructor (private _http:Http){}
   
   getAccountList(data){
        return this._http.get('http://democrm.mobilsem.com/webservicecrm.php?object=account&action=list&page='+data.page+'&size='+data.size)
            .map(res=>res.json());
   }
   
   getAccountNextList(){
        return this._http.get('http://democrm.mobilsem.com/webservicecrm.php?object=account&action=list&page=1&size=5')
            .map(res=>res.json());
   }

   getAccountPrevList(){
        return this._http.get('http://democrm.mobilsem.com/webservicecrm.php?object=account&action=list&page=1&size=5')
            .map(res=>res.json());
   }

   getCountryList(){
        return this._http.get('http://democrm.mobilsem.com/webservicecrm.php?object=country&action=list')
            .map(res=>res.json());
   }
    
   getAccountInfo(id){
      return this._http.post('http://democrm.mobilsem.com/webservicecrm.php?object=account&action=get&id_account='+id)
            .map(res=>res.json());
   }

   addAccount(data){
      //var headers = new Headers();
      //header.append('Content-Type','application/x-www-form-urlencoded');
      var url = 'http://democrm.mobilsem.com/webservicecrm.php?object=account&action=add&'+data.params;
      var param = 'action=add'
      return this._http.post(url).map(res=>res.json());
   }
   
   UpdateAccount(data){
      //var headers = new Headers();
      //header.append('Content-Type','application/x-www-form-urlencoded');
      var url = 'http://democrm.mobilsem.com/webservicecrm.php?object=account&action=update&'+data.params;
      var param = 'action=add'
      return this._http.post(url).map(res=>res.json());
   }
}