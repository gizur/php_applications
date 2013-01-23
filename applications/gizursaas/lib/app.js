var TodosModel = Stapes.subclass();

var TodosView = Stapes.subclass({
    constructor : function(model) {
        var self = this;

        this.$el = $("#inputform");
        this.model = model

        var $input = this.$el.find("input");

        this.$el.on('submit', function(e) {
            e.preventDefault();


            self.model.push( $input.val() );
            $input.val('');
        });
    }
});

var TodosController = Stapes.subclass({
    constructor : function() {
        var self = this;
        this.model = new TodosModel();
        this.view = new TodosView( this.model );

        this.model.on('change', function(todoId) {
            var text = self.model.get( todoId );
            $("#todos ul").append('<li>' + text + '</li>');
        });
    }
});

var controller = new TodosController();