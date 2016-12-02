"use strict";

var Game = function (data) {
    this.init              = data;
    this.data              = {};
    this.in_progress       = false;
    this.autoplay          = false;
    this.autoreplay        = false;
    this.auto_restart_time = 3;
    this.replay_id         = 0;
    this.replay_id_prev    = 0;
    this.replays_loaded    = false;
    this.$scales           = $('#scales');
    this.timer             = false;

    this.templates         = {
        ball  : function (id, prop) {
            return '<div class="ball ' + prop + '" data-id="' + id + '">' + id + '</div>';
        },
        card  : function (data) {
            return '<div class="card" data-role="' + data['role'] + '"> <div class="header"> <span class="icon-scale"></span>' + data['result'] + '</div><div class="content">' + data['content'] + '</div></div>';
        },
        group : function (content) {
            return '<div class="group">' + content + "</div>";
        },
        replay: function (data) {
            return '<div class="replay">' + data['name'] + ' <span class="button" data-id="' + data['id'] + '">Replay</span></div>';
        }
    };

    this.start();
};

Game.prototype = {
    start               : function () {
        var self        = this;
        var $header     = $('header');
        var $autoplay   = $header.find('span[data-role=autoplay]');
        this.autoplay   = $autoplay.hasClass('selected');
        var $autoreplay = $header.find('span[data-role=autoreplay]');
        this.autoreplay = $autoreplay.hasClass('selected');
        var data        = this.init['init'];
        var content     = '';
        var template    = this.getTemplate('ball');

        for (var prop in data) {
            if (!data.hasOwnProperty(prop)) {
                continue;
            }

            var ball = data[prop];
            var type = 'init';
            content += template(prop, type);
        }

        this.hideAutoRestart();
        this.hideStartAgainButton();
        this.setContent('init', content);
        this.clearScales();
        this.initReplays();

        var $items_init = $('div[data-role=init]').find('.ball.init');
        $items_init.off();

        //set start event
        var id;
        var $replay = $('.replay');
        if (!this.autoplay && !this.autoreplay) {
            if (this.replay_id) {
                self.initBalls(this.replay_id, true);
            }
            else {
                $items_init.on('click', function () {
                    id = $(this).data('id');
                    $items_init.off();
                    self.initBalls(id);
                });
            }
        }
        else if (this.autoplay) {
            id = parseInt(Math.random() * 9 + 1);
            self.initBalls(id);
        }
        else if (this.autoreplay) {
            var first_replay  = 0;
            var old_replay_id = parseInt(this.replay_id_prev);
            var $replays      = $replay.find('.button');
            $replays.each(function () {
                var id = parseInt($(this).data('id'));
                if (!first_replay) {
                    first_replay = id;
                }
                //going from top to bottom replays and auto repeat the loop
                if (!self.replay_id_prev || id < self.replay_id_prev) {
                    self.replay_id_prev = id;

                    return false;
                }
            });

            if (old_replay_id == this.replay_id_prev) {
                this.replay_id_prev = first_replay;
            }

            if (this.replay_id_prev) {
                $replay.removeClass('selected').find('span[data-id=' + this.replay_id_prev + ']').parent().addClass('selected');
                this.initBalls(this.replay_id_prev, true);
            }
        }
    },
    initBalls           : function (id, load) {
        this.in_progress = true;
        var self         = this;
        var command      = load ? 'load/' : 'selected/';

        $.getJSON('/get_data/' + command + id, function (data) {
            self.data = data;
            var id    = parseInt(self.data['selected']);

            self.chooseBall(id);
            self.showScales('scale_one');
            self.showScales('scale_two');
            self.showResult('scale_two');

            //add replay of current game
            if (self.data['replay']) {
                var replay = self.getTemplate('replay')(self.data['replay']);
                self.prependContent('replays', replay);
                self.setReplayEvents();
            }

            self.in_progress = false;
            self.tryRestart();
        });
    },
    initReplays         : function () {
        if (!this.replays_loaded) {
            this.replays_loaded = true;
            var template        = this.getTemplate('replay');
            var replays         = this.init['replays'];
            var content         = '';
            replays.map(function (replay) {
                content += template(replay);
            });
            this.setContent('replays', content);
            this.setReplayEvents();
        }
    },
    setReplayEvents     : function () {
        //set events
        var self     = this;
        var $replays = $('.replay');
        var $buttons = $replays.find('.button');
        $buttons.off();
        $buttons.on('click', function () {
            if (!self.inProgress()) {
                if (self.autoplay || self.autoreplay) {
                    $('header').find('.button').removeClass('selected');
                    self.hideAutoRestart();
                }

                $replays.removeClass('selected');
                $(this).parent().addClass('selected');

                self.replay_id = $(this).data('id');
                self.start();
            }
        });
    },
    unselectReplays     : function () {
        $('.replay').removeClass('selected');
        this.replay_id = 0;
    },
    tryRestart          : function () {
        if (this.autoplay) {
            this.showAutoRestart('Auto play');
        }
        else if (this.autoreplay) {
            this.showAutoRestart('Auto replay');
        }
        else {
            this.showStartAgainButton();
        }
    },
    showAutoRestart     : function (text) {
        var seconds_left = this.auto_restart_time;
        var self         = this;
        var content      = function (text, count) {
            return text + ' will restart in ' + count;
        };

        this.setContent('autorestart', content(text, seconds_left));
        $('#autorestart').show();
        seconds_left--;
        self.timer = setInterval(function () {
            self.setContent('autorestart', content(text, seconds_left));

            if (seconds_left-- <= 0) {
                clearInterval(self.timer);
                self.start();
            }
        }, 1000);
    },
    hideAutoRestart     : function () {
        $('#autorestart').hide();
        clearInterval(this.timer);
    },
    chooseBall          : function (id) {
        $('div[data-role=init]').find('.ball').each(function () {
            $(this).removeClass('init');
            if ($(this).data('id') == id) {
                $(this).addClass('red');
            }
        });
    },
    showScales          : function (name) {
        var card_tmpl    = this.getTemplate('card');
        var group_tmpl   = this.getTemplate('group');
        var scales       = this.data[name]['groups'];
        var result       = scales['result'];
        var card_content = '';

        for (var i = 1; i <= 2; i++) {
            var group = scales['group_' + i];
            card_content += group_tmpl(this.renderScaleContent(group['items']));
        }

        this.addContent('scales', card_tmpl({role: name, content: card_content, result: result}))
    },
    showResult          : function (name) {
        var card_tmpl    = this.getTemplate('card');
        var item         = this.data[name]['heaviest'];
        var result       = 'The heaviest ball is';
        var card_content = this.renderScaleContent(item);

        this.addContent('scales', card_tmpl({role: 'result', content: card_content, result: result}))
    },
    renderScaleContent  : function (items) {
        var scale_content = '';
        var ball_tmpl     = this.getTemplate('ball');
        for (var prop in items) {
            if (!items.hasOwnProperty(prop)) {
                continue;
            }
            var type = items[prop] ? 'red' : '';
            scale_content += ball_tmpl(prop, type);
        }

        return scale_content;
    },
    clearScales         : function () {
        this.$scales.html('');
    },
    getTemplate         : function (name) {
        if (this.templates.hasOwnProperty(name)) {
            return this.templates[name];
        }
    },
    setContent          : function (role, content) {
        $('div[data-role=' + role + ']').find('.content').html(content);
    },
    prependContent      : function (role, content) {
        $('div[data-role=' + role + ']').find('.content').prepend(content);
    },
    addContent          : function (id, content) {
        $('#' + id).append(content);
    },
    inProgress          : function () {
        return this.in_progress;
    },
    hideStartAgainButton: function () {
        $('#play_again').hide();
    },
    showStartAgainButton: function () {
        $('#play_again').show();
    }
};

$(function () {
    $.getJSON('/get_data', function (data) {
        var game = new Game(data);
        game.hideStartAgainButton();
        $('header').find('.button').on('click', function () {
            if (!game.inProgress()) {
                $(this).toggleClass('selected');
                if ($(this).hasClass('selected')) {
                    var role = $(this).data('role');
                    $('header').find('.button').each(function () {
                        if ($(this).data('role') != role) {
                            $(this).removeClass('selected');
                        }
                    });
                    game.hideStartAgainButton();
                    game.unselectReplays();
                    game.start();
                }
                else {
                    game.hideAutoRestart();
                    game.unselectReplays();
                    game.showStartAgainButton();
                }
            }
        });

        $('#play_again').click(function () {
            game.unselectReplays();
            game.start();
        });
    });
});