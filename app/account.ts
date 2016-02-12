import {Component} from 'angular2/core';
import {HTTPTestService} from './http-test-service';

@Component({
  // Declare the tag name in index.html to where the component attaches
  selector: 'axess-app',
  // Location of the template for this component
  templateUrl: 'templates/list_account.html',
  providers: [HTTPTestService]
})
export class accountMod {
    public getData = []
    public getCountryData = []
    public accountInfoStore = []

    public accountname = "";
    public email = "";
    public phone = "";
    public address1 = "";
    public address2 = "";
    public city = "";
    public zipcode = "";
    public countryid = "";
    
    // variable to keep track of record count
    private pageNumber = 1;
    // Number of records per page
    private activityPerAjax = 8; 
    private startActivityCount = 1;
    private lastActivityCount = this.activityPerAjax;
    
    constructor (private _httpService:HTTPTestService){ 
        this.onloadAccountList(); 
    }
    
    
    setGlobalVars(data){
            var gVars = (data==0) ? "" : data.records[0];
            this.accountname = (data==0) ? gVars : gVars.accountname;
            this.email = (data==0) ? gVars : gVars.email;
            this.phone = (data==0) ? gVars : gVars.phone;
            this.address1 = (data==0) ? gVars : gVars.address1;
            this.address2 = (data==0) ? gVars : gVars.address2;
            this.city =  (data==0) ? gVars : gVars.city;
            this.zipcode = (data==0) ? gVars : gVars.zipcode;
            this.countryid = (data==0) ? gVars : gVars.countryid;
            this.countryname = (data==0) ? gVars : gVars.countryname;
    }


    onloadAccountList(){
        var data = {
          'page' : this.pageNumber,  
          'size' : this.activityPerAjax  
        };
        this._httpService.getAccountList(data)
            .subscribe(
                data=>this.getData=data,
                error=>alert(error),
                ()=> this.getPageCount()
            )
    }
    
    getPageCount(){
        this.getData.currentActivity = this.startActivityCount+'-'+this.lastActivityCount+'/'+this.getData.totalrecords;
        console.log(this.getData); 
        this.getData.currentPage = this.pageNumber+'/'+ Math.ceil(this.getData.totalrecords/this.activityPerAjax);
    }
    
    onloadCountryList(){
        this._httpService.getCountryList()
            .subscribe(
                data=>this.getCountryData=data,
                error=>alert(error),
                ()=>console.log("finsh")
        )
    }
    
   // function to fetch previous records 
    pageprev(){
        //check if already on first page
        if((this.startActivityCount == 1)){
            return false;
        }
        this.pageNumber = this.pageNumber-1;
        this.startActivityCount = this.startActivityCount - this.activityPerAjax;
        this.lastActivityCount = this.lastActivityCount - this.activityPerAjax;
        // check if last activity count exceed total number of records
        this.lastActivityCount = (this.lastActivityCount > this.getData.totalrecords) ? this.getData.totalrecords : this.lastActivityCount;
        this.onloadAccountList()
    }
    
    // function to fetch next records 
    pagenext(){
        //check if already on last page
        if((this.lastActivityCount == this.getData.totalrecords)){
            return false;
        }
        this.pageNumber = this.pageNumber+1;
        this.startActivityCount = this.startActivityCount + this.activityPerAjax;
        this.lastActivityCount = this.lastActivityCount + this.activityPerAjax;
        // check if last activity count exceed total number of records
        this.lastActivityCount = (this.lastActivityCount > this.getData.totalrecords) ? this.getData.totalrecords : this.lastActivityCount;
        this.onloadAccountList()
    }
    
    onAddAccount(){
        this.setGlobalVars(0);
        this.onloadCountryList();
    }
    
    onViewAccount(id){
         this._httpService.getAccountInfo(id)
            .subscribe(
                data=>this.setGlobalVars(data),
                error=>alert(error),
                ()=>console.log("finsh")
        )
//        this.setGlobalVars(0);
        this.onloadCountryList();
    }
    onEditAccount(id){
        this._httpService.getAccountInfo(id)
            .subscribe(
                data=>this.accountInfoStore=data,
                error=>alert(error),
                ()=>this.setGlobalVars(this.accountInfoStore);
            )
        //set into variable
        this.onloadCountryList();
    }
}