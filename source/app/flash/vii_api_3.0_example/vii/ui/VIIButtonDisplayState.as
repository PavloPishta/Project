package vii.ui {
  import flash.display.Sprite;
  import flash.display.Shape;

  /**
   * @author Andrew Rogozov
   */
  internal class VIIButtonDisplayState extends Sprite {
    private var bgColor:uint;
    public function VIIButtonDisplayState(bgColor:uint, width: uint, height: uint) {
        this.bgColor = bgColor;
        draw(width, height);
    }

    private function draw(width: Number, height: Number):void {
      var child: Shape = new Shape();
      child.graphics.beginFill(bgColor);
      child.graphics.drawRoundRect(0, 0, width, height, 5);
      child.graphics.endFill();
      addChild(child);
    }
  }
}
