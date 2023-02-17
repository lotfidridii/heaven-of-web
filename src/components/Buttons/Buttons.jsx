import "./Buttons.css";
function Buttons(props) {
  return (
    <a class="btn" href={props.href}>
      {props.btnName}
    </a>
  );
}
export default Buttons;
