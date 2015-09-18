/*
 * Code Owner:
 * Modified Date: 09/07/2015
 * Modified By: Phong Lam
 */
(function(){
    'use strict';
    var app = angular.module("app.fireBase.Ctrl", []);

    app.controller("ProjectController", function(){
        /*
        *   "$scope" issue within nested controller???
        *   Let use the "this" keyword, instead of "$scope"
        */
        var self = this;
        self.Name = "PVLam";
    });
}).call();