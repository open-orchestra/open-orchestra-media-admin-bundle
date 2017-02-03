import MediaSubApplication from './Application/MediaSubApplication'

$(() => {
    Backbone.Events.on('application:before:start', () => {
        MediaSubApplication.run();
    });
});
