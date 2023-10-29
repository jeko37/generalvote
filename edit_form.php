<?php

class block_generalvote_edit_form extends block_edit_form {
        
    protected function specific_definition($mform) {
        global $DB;

        $instance = $DB->get_record('block_instances', array('id' => $this->block->context->instanceid));
        $block = block_instance('generalvote', $instance);
        
        //Check data
        $votes = $block->get_vote_data();

        if(count($votes)>0) {
            $mform->addElement('header', 'config_header', get_string('blocksettings', 'block'));
            $mform->addElement('static', 'my_text', get_string('disableconfig', 'block_generalvote'));
        }
        else
        {
            $mform->addElement('header', 'config_header', get_string('blocksettings', 'block'));

            $mform->addElement('text', 'config_title', get_string('questiontitle', 'block_generalvote'));
            $mform->setDefault('config_title', '');
            $mform->setType('config_title', PARAM_RAW);
            
            $mform->addElement('textarea', 'config_text', get_string('questiontext', 'block_generalvote'));
            $mform->setDefault('config_text', '');
            $mform->setType('config_text', PARAM_RAW);

            // Option list
            for ($i = 1; $i <= 5; $i++) {
                $name = "config_option_$i";
                $label = get_string("option$i", 'block_generalvote');
            
                $mform->addElement('text', $name, $label);
                $mform->setDefault($name, '');
                $mform->setType($name, PARAM_RAW);
            }
        }
    }
}