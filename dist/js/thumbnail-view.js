(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
"use strict";

jQuery(document).ready(function ($) {
  if (typeof wp.media.view.Attachment !== 'undefined') {
    wp.media.view.Attachment.prototype.template = wp.media.template('attachment-custom');
  }
});
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbInRodW1ibmFpbC12aWV3LmpzIl0sIm5hbWVzIjpbImpRdWVyeSIsImRvY3VtZW50IiwicmVhZHkiLCIkIiwid3AiLCJtZWRpYSIsInZpZXciLCJBdHRhY2htZW50IiwicHJvdG90eXBlIiwidGVtcGxhdGUiXSwibWFwcGluZ3MiOiI7O0FBQUFBLE1BQU0sQ0FBQ0MsUUFBRCxDQUFOLENBQWlCQyxLQUFqQixDQUF1QixVQUFVQyxDQUFWLEVBQWE7QUFDbEMsTUFBSSxPQUFPQyxFQUFFLENBQUNDLEtBQUgsQ0FBU0MsSUFBVCxDQUFjQyxVQUFyQixLQUFvQyxXQUF4QyxFQUFxRDtBQUNuREgsSUFBQUEsRUFBRSxDQUFDQyxLQUFILENBQVNDLElBQVQsQ0FBY0MsVUFBZCxDQUF5QkMsU0FBekIsQ0FBbUNDLFFBQW5DLEdBQThDTCxFQUFFLENBQUNDLEtBQUgsQ0FBU0ksUUFBVCxDQUFrQixtQkFBbEIsQ0FBOUM7QUFDRDtBQUNGLENBSkQiLCJzb3VyY2VzQ29udGVudCI6WyJqUXVlcnkoZG9jdW1lbnQpLnJlYWR5KGZ1bmN0aW9uICgkKSB7XG4gIGlmICh0eXBlb2Ygd3AubWVkaWEudmlldy5BdHRhY2htZW50ICE9PSAndW5kZWZpbmVkJykge1xuICAgIHdwLm1lZGlhLnZpZXcuQXR0YWNobWVudC5wcm90b3R5cGUudGVtcGxhdGUgPSB3cC5tZWRpYS50ZW1wbGF0ZSgnYXR0YWNobWVudC1jdXN0b20nKTtcbiAgfVxufSk7XG4iXX0=
},{}]},{},[1]);

//# sourceMappingURL=data:application/json;charset=utf8;base64,eyJ2ZXJzaW9uIjozLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiIiwic291cmNlcyI6WyJ0aHVtYm5haWwtdmlldy5qcyJdLCJzb3VyY2VzQ29udGVudCI6WyIoZnVuY3Rpb24gZSh0LG4scil7ZnVuY3Rpb24gcyhvLHUpe2lmKCFuW29dKXtpZighdFtvXSl7dmFyIGE9dHlwZW9mIHJlcXVpcmU9PVwiZnVuY3Rpb25cIiYmcmVxdWlyZTtpZighdSYmYSlyZXR1cm4gYShvLCEwKTtpZihpKXJldHVybiBpKG8sITApO3ZhciBmPW5ldyBFcnJvcihcIkNhbm5vdCBmaW5kIG1vZHVsZSAnXCIrbytcIidcIik7dGhyb3cgZi5jb2RlPVwiTU9EVUxFX05PVF9GT1VORFwiLGZ9dmFyIGw9bltvXT17ZXhwb3J0czp7fX07dFtvXVswXS5jYWxsKGwuZXhwb3J0cyxmdW5jdGlvbihlKXt2YXIgbj10W29dWzFdW2VdO3JldHVybiBzKG4/bjplKX0sbCxsLmV4cG9ydHMsZSx0LG4scil9cmV0dXJuIG5bb10uZXhwb3J0c312YXIgaT10eXBlb2YgcmVxdWlyZT09XCJmdW5jdGlvblwiJiZyZXF1aXJlO2Zvcih2YXIgbz0wO288ci5sZW5ndGg7bysrKXMocltvXSk7cmV0dXJuIHN9KSh7MTpbZnVuY3Rpb24ocmVxdWlyZSxtb2R1bGUsZXhwb3J0cyl7XG5cInVzZSBzdHJpY3RcIjtcblxualF1ZXJ5KGRvY3VtZW50KS5yZWFkeShmdW5jdGlvbiAoJCkge1xuICBpZiAodHlwZW9mIHdwLm1lZGlhLnZpZXcuQXR0YWNobWVudCAhPT0gJ3VuZGVmaW5lZCcpIHtcbiAgICB3cC5tZWRpYS52aWV3LkF0dGFjaG1lbnQucHJvdG90eXBlLnRlbXBsYXRlID0gd3AubWVkaWEudGVtcGxhdGUoJ2F0dGFjaG1lbnQtY3VzdG9tJyk7XG4gIH1cbn0pO1xuLy8jIHNvdXJjZU1hcHBpbmdVUkw9ZGF0YTphcHBsaWNhdGlvbi9qc29uO2NoYXJzZXQ9dXRmLTg7YmFzZTY0LGV5SjJaWEp6YVc5dUlqb3pMQ0p6YjNWeVkyVnpJanBiSW5Sb2RXMWlibUZwYkMxMmFXVjNMbXB6SWwwc0ltNWhiV1Z6SWpwYkltcFJkV1Z5ZVNJc0ltUnZZM1Z0Wlc1MElpd2ljbVZoWkhraUxDSWtJaXdpZDNBaUxDSnRaV1JwWVNJc0luWnBaWGNpTENKQmRIUmhZMmh0Wlc1MElpd2ljSEp2ZEc5MGVYQmxJaXdpZEdWdGNHeGhkR1VpWFN3aWJXRndjR2x1WjNNaU9pSTdPMEZCUVVGQkxFMUJRVTBzUTBGQlEwTXNVVUZCUkN4RFFVRk9MRU5CUVdsQ1F5eExRVUZxUWl4RFFVRjFRaXhWUVVGVlF5eERRVUZXTEVWQlFXRTdRVUZEYkVNc1RVRkJTU3hQUVVGUFF5eEZRVUZGTEVOQlFVTkRMRXRCUVVnc1EwRkJVME1zU1VGQlZDeERRVUZqUXl4VlFVRnlRaXhMUVVGdlF5eFhRVUY0UXl4RlFVRnhSRHRCUVVOdVJFZ3NTVUZCUVVFc1JVRkJSU3hEUVVGRFF5eExRVUZJTEVOQlFWTkRMRWxCUVZRc1EwRkJZME1zVlVGQlpDeERRVUY1UWtNc1UwRkJla0lzUTBGQmJVTkRMRkZCUVc1RExFZEJRVGhEVEN4RlFVRkZMRU5CUVVORExFdEJRVWdzUTBGQlUwa3NVVUZCVkN4RFFVRnJRaXh0UWtGQmJFSXNRMEZCT1VNN1FVRkRSRHRCUVVOR0xFTkJTa1FpTENKemIzVnlZMlZ6UTI5dWRHVnVkQ0k2V3lKcVVYVmxjbmtvWkc5amRXMWxiblFwTG5KbFlXUjVLR1oxYm1OMGFXOXVJQ2drS1NCN1hHNGdJR2xtSUNoMGVYQmxiMllnZDNBdWJXVmthV0V1ZG1sbGR5NUJkSFJoWTJodFpXNTBJQ0U5UFNBbmRXNWtaV1pwYm1Wa0p5a2dlMXh1SUNBZ0lIZHdMbTFsWkdsaExuWnBaWGN1UVhSMFlXTm9iV1Z1ZEM1d2NtOTBiM1I1Y0dVdWRHVnRjR3hoZEdVZ1BTQjNjQzV0WldScFlTNTBaVzF3YkdGMFpTZ25ZWFIwWVdOb2JXVnVkQzFqZFhOMGIyMG5LVHRjYmlBZ2ZWeHVmU2s3WEc0aVhYMD1cbn0se31dfSx7fSxbMV0pO1xuIl0sImZpbGUiOiJ0aHVtYm5haWwtdmlldy5qcyJ9