/*
 * Code Owner: Cnit*
 * Modified Date: 10/9/2015
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

    app.directive("directiveName", function(){
        return {
            restrict: 'A',
            link: function($element, $attr){

            }
        };
    });
}).call(angular);