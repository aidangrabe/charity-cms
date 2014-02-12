<?php

class Charity extends Eloquent {
    const TABLE_NAME = 'charities';

    protected $primaryKey = 'charity_id';

    public static $rules = array(
        'name'              => "required|min:2|unique:charities",
        'description'       => 'required|min:2',
        'charity_category_id'  => 'required|exists:charity_categories,charity_category_id'
    );

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = self::TABLE_NAME;

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = array();

    protected $guarded = array('charity_id');
    protected $fillable = array();

    public function category() {
        return $this->hasOne('CharityCategory', 'charity_category_id');
    }

    public static function make($data) {
        $charity = new Charity();
        $charity->fill($data);
        return $charity;
    }

    public static function validate($data) {
        return Validator::make($data, self::$rules);
    }

}
