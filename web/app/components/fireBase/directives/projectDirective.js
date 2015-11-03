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

    app.directive("projectListLayout", function(){
        return {
            restrict: 'E',
            replace: true,
            templateUrl: './app/components/fireBase/views/list.html',
            link: function(element, attr){
                /*element.bind('click', function(){
                    element.css('background-color', 'purple');
                });
                element.bind('mouseover', function(){
                    element.css('cursor', 'pointer');
                });*/
            }
        };
    });

    app.directive("projectPage", function(){
        return {
            restrict: 'E',
            templateUrl: './app/components/fireBase/views/default.html'
        };
    });
}).call(angular);