import { Zoom } from "react-reveal";
import "./SectionHeader.css";
function SectionHeader(props) {
  return (
    <Zoom>
      <div className={props.Align}>
        <div className="section-header">
          <h4>{props.SectionSubTitle}</h4>
          <h2>{props.SectionTitle}</h2>
        </div>
      </div>
    </Zoom>
  );
}

export default SectionHeader;
