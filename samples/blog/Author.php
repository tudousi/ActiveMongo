<?php

class AuthorModel extends ActiveMongo
{
    public $username;
    public $name;

    function getCollectionName()
    {
        return 'author';
    }

    /**
     *  Username filter.
     *
     *  - It must be unique (handled by MongoDB actually).
     *  - It can't be changed.
     *  - It must be /[a-z][a-z0-9\-\_]+/
     *  - It must be longer than 5 letters.
     *
     *  @return bool
     */
    function username_filter($value, $old_value)
    {
        if ($old_value!=null && $value != $old_value) {
            throw new FilterException("The username can't be changed");
        }

        if (!preg_match("/[a-z][a-z0-9\-\_]+/", $value)) {
            throw new FilterException("The username is not valid");
        }

        if (strlen($value) < 5) {
            throw new Exception("Username too short");
        }

        return true;
    }

    /**
     *  When an User updates his profile, we need to 
     *  make sure that every post written by him is also
     *  updated with his name and username.
     *
     *  @return void
     */
    function on_update()
    {
        $post = new PostModel;
        $post->updateAuthorInfo($this->getID());
    }

    function setup()
    {
        $collection = & $this->_getCollection();
        $collection->ensureIndex(array('username' => 1), array('unique'=> 1, 'background' => 1));
    }
}