/*
 * Code Owner: Cnit*
 * Modified Date: 09/07/2015
 * Modified By: Phong Lam
 */
(function(){
    'use strict';
    var app= angular.module("com.FireBase");

    app.controller("ProjectController", ["fbProject", "$scope", "$routeParams", function(fbProject, $scope, $routeParams){
        /*
        *   "$scope" issue within nested controller???
        *   Let use the "this" keyword, instead of "$scope"
        *   angular.extend(this, {
                name: "PVLam",
                getName: function(){
                    return this.name;
                }
            });
        */
        $scope.getList = function(){
            return fbProject.fetch();
        };

        $scope.addItem = function(data){
            fbProject.persist();
        };

        $scope.editItem = function(data){
            fbProject.persist();
        };

        $scope.setProjectName = function(name){
            $scope.ProjectName = name;
        };
    }]);
}).call(angular);