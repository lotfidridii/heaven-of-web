import Typed from "react-typed";
function TypedText() {
  return (
    <div className="typed-text">
      <Typed strings={["Conception de sites Web", "Référencement Naturel", "Conception graphique", "Stratégie de contenu"]} typeSpeed={50} backSpeed={50} loop/>
    </div>
  );
}

export default TypedText;
