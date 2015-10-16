/*
 * Code Owner: Cnit*
 * Modified Date: 09/07/2015
 * Modified By: Phong Lam
 */
(function(){
    'use strict';
    var app;
    if(angular.isDefined(angular.module("app.fireBase"))){
        app = angular.module("app.fireBase");
    } else {
        app = angular.module("app.fireBase", ["firebase"]);
    }
    app.controller("ProjectController", ["fbProject", function(fbProject){
        /*
        *   "$scope" issue within nested controller???
        *   Let use the "this" keyword, instead of "$scope"
        */
        var obj = fbProject.fetch();
        angular.extend(this, {
            Name: "PVLam",
            MyFunc: function myFunc(name){
                this.Name = name;
            }
        });
    }]);
}).call(angular);