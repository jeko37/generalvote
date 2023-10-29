<?php

class block_generalvote extends block_base {
    public function init() {
        $this->title = get_string('pluginname', 'block_generalvote');
    }

    public function instance_allow_multiple() {
        return true;
    }

    public function check_user_vote()
    {
        global $DB;
        global $USER;

        $vote = $DB->get_record('block_generalvote_votes', array('userid' => $USER->id, 'questionid' => $this->instance->id));

        if($vote)
        {
            return true;
        }

        return false;
    }

    public function get_vote_data()
    {
        global $DB;
        $votes = $DB->get_records('block_generalvote_votes', array('questionid' => $this->instance->id));
        return $votes;
    }

    public function render_results()
    {
        $votes = json_decode(json_encode($this->get_vote_data()),true);
        $results = [];
        $total = 0;
        $out = '<div class="vote_bar" style="margin-top:10px;">';

        //Prepare initial result array
        for ($i = 1; $i <= 5; $i++) {
            $el = 'option_'.$i;
            if(!empty($this->config->$el))
            {
                $results[$this->config->$el] = 0;
            }
        }

        //Count all votes
        foreach ($votes as $reg) {
            if(isset($results[$reg['vote']]))
            {
                $results[$reg['vote']] += 1;
                $total++;
            }
        }

        //Render final HTML
        if($total > 0)
        {
            foreach($results as $key => $reg) {
                $out .= '<span><b>'.$key.'</b> <small>('.$reg.' '.get_string('votestext', 'block_generalvote').')</small></span><div style="height:20px; width:'.($reg * 100 / $total).'%; background: #0F6CBF; border-right:1px solid #000; margin-bottom:10px;text-align:center;color:#FFF;font-size:12px;">'.($reg * 100 / $total).'%</div>';
            }
        }
        else
        {
            $out .= '<p>'.get_string('defaultinitialtext', 'block_generalvote').'</p>';
        }

        return $out.'</div>';
    }

    public function get_content() {
        global $DB;
        global $CFG;

        if ($this->content !== null) {
          return $this->content;
        }

        $this->content = new stdClass;

        if(!empty($this->config->title))
        {
            $this->title = $this->config->title;
        }
        else
        {
            $this->title = get_string('defaulttitle', 'block_generalvote');   
        }

        if(!empty($this->config->text))
        {
            $this->content->text = $this->config->text;
        }
        else
        {
            $this->content->text = get_string('defaulttext', 'block_generalvote');   
        }

        // Vote area
        $this->content->text .= '<div id="vote_area_'.$this->instance->id.'">';

        for ($i = 1; $i <= 5; $i++) {
            $el = 'option_'.$i;

            if(!empty($this->config->$el))
            {
                $this->content->text .= '<label class="poll_'.$this->instance->id.'"><input type="radio" '.(($i==1) ? 'checked' : '').' value="'.$this->config->$el.'" name="poll_option_'.$this->instance->id.'">'.$this->config->$el.'</label>';
            }
        }

        $this->content->text .= '<br><a href="#" onclick="javascript:votar_block_'.$this->instance->id.'();">'.get_string('votelinktext', 'block_generalvote').'</a><div style="color:red;font-size:10px;" id="error_message_'.$this->instance->id.'"></div></div>';

        // Result Area
        $this->content->text .= '<div id="results_area_'.$this->instance->id.'">'.$this->render_results().'</div>';

        //Styles CSS
        $this->content->text .= '<style>.poll_'.$this->instance->id.':first-child {margin-top:20px;} .poll_'.$this->instance->id.' {display:block;} .poll_'.$this->instance->id.' > input {margin-right: 10px;}</style>';     
        
        //Javascript
        $this->content->text .= '<script>
        function votar_block_'.$this->instance->id.'() {
            var data = {
                action: "vote",
                vote: $("input[name=poll_option_'.$this->instance->id.']:checked").val(),
                question: '.$this->instance->id.'
            };

            jQuery.post("'.$CFG->wwwroot.'/blocks/generalvote/ajax.php", data, function(res) {
                try {
                    var response = JSON.parse(res);
                    if (response.process === true) {
                        $("#results_area_'.$this->instance->id.'").html(response.html_results);
                        $("#vote_area_'.$this->instance->id.'").hide();
                        $("#results_area_'.$this->instance->id.'").fadeIn();
                    } else {
                        $("#error_message_'.$this->instance->id.'").html("'.get_string('errorajaxrequest', 'block_generalvote').'");
                    }
                } catch (error) {
                    $("#error_message_'.$this->instance->id.'").html("'.get_string('errorajaxrequest', 'block_generalvote').'");
                }
            });
        }</script>';

        // checks if the user has already voted
        if($this->check_user_vote())
        {
            $this->content->text .= '<style>#vote_area_'.$this->instance->id.' {display:none;}</style>';
        }
        else
        {
            $this->content->text .= '<style>#results_area_'.$this->instance->id.' {display:none;}</style>';
        }
        
        return $this->content;
    }
}