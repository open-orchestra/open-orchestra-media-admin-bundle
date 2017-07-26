import MediaSubApplication from 'OpenOrchestra/Application/MediaSubApplication'

$(() => {
    Backbone.Events.on('application:before:start', () => {
        MediaSubApplication.run();
    });
});
