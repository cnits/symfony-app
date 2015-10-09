/*
 * Code Owner: Cnit*
 * Modified Date: 05/27/2015
 * Modified By: Phong Lam
 */
'use strict';
(function(){
    var app = angular.module('app', ['ngRoute', "app.fireBase"])
    .config(function($routeProvider) {
        $routeProvider
            .when('/', {
                controller:'ProjectListController as projectList',
                templateUrl:'/app/components/fireBase/views/list.html',
                resolve: {
                    projects: function (Projects) {
                        console.log(4);
                        return Projects.fetch();
                    }
                }
            })
            .when('/edit/:projectId', {
                controller:'EditProjectController as editProject',
                templateUrl:'/components/fireBase/views/detail.html'
            })
            .when('/new', {
                controller:'NewProjectController as editProject',
                templateUrl:'/components/fireBase/views/detail.html'
            })
            .otherwise({
                redirectTo:'/'
            });
    })

    .controller('ProjectListController', function(projects) {
        var projectList = this;console.log(5);
        projectList.projects = projects;
    })

    .controller('NewProjectController', function($location, Projects) {
        var editProject = this;console.log(6);
        editProject.save = function() {
            Projects.$add(editProject.project).then(function(data) {
                $location.path('/');
            });
        };
    })

    .controller('EditProjectController',
        function($location, $routeParams, Projects) {
            var editProject = this;console.log(7);
            var projectId = $routeParams.projectId,
                projectIndex;

            editProject.projects = Projects.projects;
            projectIndex = editProject.projects.$indexFor(projectId);
            editProject.project = editProject.projects[projectIndex];

            editProject.destroy = function() {
                editProject.projects.$remove(editProject.project).then(function(data) {
                    $location.path('/');
                });
            };

            editProject.save = function() {
                editProject.projects.$save(editProject.project).then(function(data) {
                    $location.path('/');
                });
            };
        }
    );
}).call();