<?php
namespace org\opencomb\doccenter\formatter;

abstract class AbstractSingleLineTransformer implements ITransformer {
	public function lineType() {
		return self::SINGLE_LINE;
	}
}
