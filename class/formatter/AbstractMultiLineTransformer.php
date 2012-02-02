<?php
namespace org\opencomb\doccenter\formatter;

abstract class AbstractMultiLineTransformer implements ITransformer {
	public function lineType() {
		return self::MULTI_LINE;
	}
}
